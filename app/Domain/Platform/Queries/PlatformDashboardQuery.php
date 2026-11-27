<?php

namespace App\Domain\Platform\Queries;

use App\Domain\Platform\Enums\BillingCycle;
use App\Domain\Platform\Enums\InvoiceStatus;
use App\Domain\Organization\Enums\OrganizationStatus;
use App\Domain\Platform\Enums\PaymentStatus;
use App\Domain\Platform\Enums\SubscriptionStatus;
use App\Domain\Platform\Models\Invoice;
use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Models\Payment;
use App\Domain\Platform\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class PlatformDashboardQuery
{
    public function get(): array
    {
        $statusCounts = Organization::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $paidActive = Subscription::query()
            ->where('status', SubscriptionStatus::Active)
            ->get(['billing_cycle', 'price']);

        $mrr = (int) round($paidActive->sum(
            fn (Subscription $s) => $s->billing_cycle === BillingCycle::Monthly ? $s->price : $s->price / 12
        ));

        $revenue = fn ($from, $to) => (int) Payment::query()
            ->where('status', PaymentStatus::Success)
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $unpaid = Invoice::query()->where('status', InvoiceStatus::Unpaid);
        $overdue = (clone $unpaid)->whereDate('due_at', '<', today());

        return [
            'stats' => [
                'organizations_total' => (int) $statusCounts->sum(),
                'organizations_by_status' => collect(OrganizationStatus::cases())
                    ->mapWithKeys(fn ($s) => [$s->value => (int) ($statusCounts[$s->value] ?? 0)]),
                'users_total' => User::count(),
                'mrr' => $mrr,
                'revenue_this_month' => $revenue(now()->startOfMonth(), now()->endOfMonth()),
                'revenue_last_month' => $revenue(now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()),
                'unpaid_count' => (clone $unpaid)->count(),
                'unpaid_amount' => (int) (clone $unpaid)->sum('total'),
                'overdue_count' => (clone $overdue)->count(),
                'overdue_amount' => (int) (clone $overdue)->sum('total'),
            ],
            'revenue_trend' => $this->revenueTrend(6),
            'plan_distribution' => Subscription::query()
                ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                ->whereIn('subscriptions.status', SubscriptionStatus::running())
                ->groupBy('plans.id', 'plans.name')
                ->orderBy('plans.name')
                ->get(['plans.name', DB::raw('COUNT(*) as total')])
                ->map(fn ($r) => ['name' => $r->name, 'total' => (int) $r->total]),
            'overdue_invoices' => (clone $overdue)
                ->with('organization:id,name')
                ->orderBy('due_at')
                ->limit(5)
                ->get()
                ->map(fn (Invoice $i) => [
                    'id' => $i->id,
                    'number' => $i->number,
                    'organization' => $i->organization?->name,
                    'total' => $i->total,
                    'due_at' => $i->due_at->toDateString(),
                    'days_late' => (int) $i->due_at->diffInDays(today()),
                ]),
            'recent_payments' => Payment::query()
                ->with(['organization:id,name', 'invoice:id,number'])
                ->where('status', PaymentStatus::Success)
                ->latest('paid_at')
                ->limit(6)
                ->get()
                ->map(fn (Payment $p) => [
                    'id' => $p->id,
                    'organization' => $p->organization?->name,
                    'invoice_id' => $p->invoice_id,
                    'invoice_number' => $p->invoice?->number,
                    'amount' => $p->amount,
                    'method' => $p->method->value,
                    'paid_at' => $p->paid_at->toIso8601String(),
                ]),
        ];
    }

    /** Pendapatan (pembayaran sukses) per bulan, N bulan terakhir termasuk bulan ini. */
    private function revenueTrend(int $months): array
    {
        $start = now()->subMonthsNoOverflow($months - 1)->startOfMonth();

        $rows = Payment::query()
            ->where('status', PaymentStatus::Success)
            ->where('paid_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        return collect(range(0, $months - 1))
            ->map(function ($i) use ($start, $rows) {
                $month = $start->copy()->addMonthsNoOverflow($i);

                return [
                    'month' => $month->format('Y-m'),
                    'label' => $month->translatedFormat('M Y'),
                    'total' => (int) ($rows[$month->format('Y-m')] ?? 0),
                ];
            })
            ->all();
    }
}

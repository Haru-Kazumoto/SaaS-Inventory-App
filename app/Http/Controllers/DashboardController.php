<?php

namespace App\Http\Controllers;

use App\Dto\Transaction\FilterIndexDto;
use App\Http\Services\AuthService;
use App\Models\Major;
use App\Models\RequestItem;
use App\Services\MonthlyReport;
use App\Services\ProductLogsService;
use App\Services\ProductsService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected TransactionService $transactionService,
        protected ProductLogsService $productLogsService,
        protected ProductsService $productService,
    ) {}

    // harus nya ini masukin ke service, tapi karna males disini aja ntar pak ade kalo rajin ke service aja ya biar lebih clean hehehe
    public function countItemFromMajor(string $major)
    {
        $major = Major::where('code', $major)->first();

        return DB::table('partial_item as pi')
            ->join('items as i', 'pi.item_id', '=', 'i.id')
            ->where('i.major_id', '=', $major->id)
            ->count();
    }

    public function indexSuperadmin()
    {
        $count_all_products = $this->productService->countFromAllMajor(); 
        $request_items = DB::table("request_item as ri")
            ->selectRaw("
                ri.id,
                ri.brand_name,
                ri.quantity,
                ri.unit,
                ri.reason_request,
                ri.request_status,
                (SELECT NAME FROM major m WHERE m.id = ri.major_id LIMIT 1) AS major_request
            ")
            ->latest('ri.created_at')
            ->paginate(10);

        

        return Inertia::render('DashboardSuperadmin', [
            'count_data' => [
                'request_item' => RequestItem::count(),
                ...$count_all_products
            ],
            'request_items' => $request_items
        ]);
    }

    public function index(Request $request)
    {
        if (Auth::user()->role->name === 'SUPERADMIN') {
            return redirect(route('dashboard.superadmin'));
        } else if (Auth::user()->role->name === 'DEVELOPER') {
            return redirect(route('dashboard.internal'));
        }

        $auth_user = Auth::user();
        $current_major = $auth_user?->major;

        $period = $request->filled('period') ? (int) $request->input('period') : Carbon::now()->year;

        $todays_borrower = $this->transactionService->getAllToday($current_major->id);
        $gradual_statistics = $this->productLogsService->getStatisticsPerYear($period, $current_major->id);

        $total_products = $this->productService->count($current_major->id);
        $total_borrowed = $this->productLogsService->countBorrowed($current_major->id);

        return Inertia::render('Dashboard', [
            'count' => [
                'total_products'    => $total_products,
                'total_borrowed'    => $total_borrowed,
                'total_request'     => RequestItem::where('major_id', $current_major->id)->count()
            ],
            'gradual_statistics'    => $gradual_statistics,
            'todays_borrower'       => $todays_borrower,
            'period'                => $period
        ]);
    }

    public function indexInternal(Request $request)
    {
        return Inertia::render("DashboardInternal");
    }
}

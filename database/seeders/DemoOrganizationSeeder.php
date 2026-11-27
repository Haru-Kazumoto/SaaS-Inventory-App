<?php

namespace Database\Seeders;

use App\Domain\Organization\Enums\OrganizationRole;
use App\Domain\Organization\Enums\OrganizationStatus;
use App\Domain\Organization\Enums\OrganizationTemplate;
use App\Domain\Organization\Enums\UnitRole;
use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Models\Unit;
use App\Domain\Platform\Enums\BillingCycle;
use App\Domain\Platform\Enums\SubscriptionStatus;
use App\Domain\Platform\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Organisasi demo (sekolah) dengan 6 unit/jurusan, akun admin organisasi & admin per unit,
 * data master dasar (satuan, kategori, gudang per unit), dan langganan aktif.
 *
 * Akun (sama dengan versi sebelumnya):
 *   superadmin / super_admin_inventory   → admin organisasi (owner)
 *   admin_<kode> / inventory_yatindo      → admin unit (tjkt, ak, te, to, tu, kkpi)
 */
class DemoOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $org = Organization::updateOrCreate(['slug' => 'smk-demo'], [
            'name' => 'SMK Demo',
            'template' => OrganizationTemplate::School,
            'status' => OrganizationStatus::Active,
            'owner_name' => 'Superadmin Management',
            'owner_email' => 'superadmin@smk-demo.local',
        ]);

        User::updateOrCreate(['username' => 'superadmin'], [
            'organization_id' => $org->id,
            'organization_role' => OrganizationRole::Owner,
            'fullname' => 'Superadmin Management',
            'email' => 'superadmin@smk-demo.local',
            'password' => 'super_admin_inventory',
        ]);

        // Data master bersama organisasi (unit_id NULL)
        foreach ([['PCS', 'Pcs'], ['UNIT', 'Unit'], ['BOX', 'Box'], ['ROLL', 'Roll'], ['METER', 'Meter'], ['SET', 'Set']] as [$code, $name]) {
            DB::table('uoms')->updateOrInsert(
                ['organization_id' => $org->id, 'code' => $code],
                ['name' => $name, 'created_at' => $now, 'updated_at' => $now],
            );
        }
        foreach ([['ALAT_PRAKTIK', 'Alat praktik'], ['ELEKTRONIK', 'Elektronik'], ['ATK', 'Alat tulis kantor'], ['BAHAN', 'Bahan habis pakai']] as [$code, $name]) {
            DB::table('categories')->updateOrInsert(
                ['organization_id' => $org->id, 'code' => $code],
                ['name' => $name, 'created_at' => $now, 'updated_at' => $now],
            );
        }

        foreach (['TJKT', 'AK', 'TE', 'TO', 'TU', 'KKPI'] as $code) {
            $unit = Unit::withoutGlobalScopes()->updateOrCreate(
                ['organization_id' => $org->id, 'code' => $code],
                ['name' => $code],
            );

            $admin = User::updateOrCreate(['username' => 'admin_'.strtolower($code)], [
                'organization_id' => $org->id,
                'organization_role' => OrganizationRole::Member,
                'fullname' => "Admin {$code} Management",
                'email' => 'admin_'.strtolower($code).'@smk-demo.local',
                'password' => 'inventory_yatindo',
            ]);
            $unit->members()->updateOrCreate(['user_id' => $admin->id], ['role' => UnitRole::Admin]);

            DB::table('warehouses')->updateOrInsert(
                ['organization_id' => $org->id, 'code' => "GUDANG_{$code}"],
                ['unit_id' => $unit->id, 'name' => "Gudang {$code}", 'created_at' => $now, 'updated_at' => $now],
            );
        }

        $plan = Plan::where('code', 'school')->first();
        if ($plan && ! $org->subscriptions()->exists()) {
            $org->subscriptions()->create([
                'plan_id' => $plan->id,
                'billing_cycle' => BillingCycle::Yearly,
                'status' => SubscriptionStatus::Active,
                'price' => 0,
                'current_period_start' => today(),
                'current_period_end' => today()->addYear()->subDay(),
            ]);
        }
    }
}

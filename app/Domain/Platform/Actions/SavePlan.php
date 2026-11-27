<?php

namespace App\Domain\Platform\Actions;

use App\Domain\Platform\Data\PlanData;
use App\Domain\Platform\Models\Plan;

/** Buat/ubah paket. Harga baru hanya berlaku untuk langganan berikutnya (harga langganan terkunci). */
final class SavePlan
{
    public function handle(PlanData $data, ?Plan $plan = null): Plan
    {
        if ($plan) {
            $plan->update($data->toArray());

            return $plan;
        }

        return Plan::create($data->toArray());
    }
}

<?php


namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Pagination\LengthAwarePaginator;

class ApplicationService
{
    /**
     * Get all applications based on the plan type.
     *
     * @param string|null $planType
     * @return LengthAwarePaginator
     */
    public function getAllApplications(?string $planType): LengthAwarePaginator
    {
        $applications = Application::with('customer', 'plan')
            ->when($planType, function ($query) use ($planType) {
                $query->whereHas('plan', function ($query) use ($planType) {
                    $query->where('type', $planType);
                });
            })
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        $applications->getCollection()->transform(function ($application) {
            $data = [
                'id' => $application->id,
                'customer_full_name' => $application->customer->first_name . ' ' . $application->customer->last_name,
                'address' => $application->address_1,
                'plan_type' => $application->plan->type,
                'plan_name' => $application->plan->name,
                'state' => $application->state,
                'plan_monthly_cost' => number_format($application->plan->monthly_cost / 100, 2),
            ];

            if ($application->status === ApplicationStatus::Complete && $application->order_id !== null) {
                $data['order_id'] = $application->order_id;
            }

            return $data;
        });

        return $applications;
    }
}

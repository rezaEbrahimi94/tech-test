<?php

namespace App\Actions;

use App\Models\Application;
use App\Services\NbnOrderService;

class OrderNbnApplicationAction
{
    protected $nbnOrderService;

    /**
     * Create a new action instance.
     *
     * @param NbnOrderService $nbnOrderService
     */
    public function __construct(NbnOrderService $nbnOrderService)
    {
        $this->nbnOrderService = $nbnOrderService;
    }

    /**
     * Order an NBN application.
     *
     * @param Application $application
     * @return void
     */
    public function __invoke(Application $application): void
    {
        // Place the NBN order for the application
        $this->nbnOrderService->placeOrder($application);

    }
}
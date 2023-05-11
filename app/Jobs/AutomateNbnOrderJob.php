<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Actions\OrderNbnApplicationAction;
use App\Models\Application;

class AutomateNbnOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $application;

    /**
     * Create a new job instance.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Execute the job.
     *
     * @param OrderNbnApplicationAction $orderNbnApplicationAction
     * @return void
     */
    public function handle(OrderNbnApplicationAction $orderNbnApplicationAction): void
    {
        // Invoke the OrderNbnApplicationAction to process the NBN application
        $orderNbnApplicationAction($this->application);
    }
}

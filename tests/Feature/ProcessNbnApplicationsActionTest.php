<?php

namespace Tests\Feature;
use App\Models\Application;
use App\Models\Plan;
use App\Actions\ProcessNbnApplicationsAction;
use App\Responders\ProcessNbnApplicationsResponder;
use App\Jobs\AutomateNbnOrderJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessNbnApplicationsActionTest extends TestCase
{
    public function test_it_processes_nbn_applications()
    {
        // Create dummy applications
        $applications = Application::factory()->count(5)->state(['status' => 'order'])->create();

        // Create associated plans
        $plans = Plan::factory()->count(5)->state(['type' => 'nbn'])->create();

        // Associate plans with applications
        $applications->each(function ($application) use ($plans) {
            $application->plan_id = $plans->random()->id;
            $application->save();
        });

        // Use Queue::fake() to mock the job dispatching
        Queue::fake();

        // Create an instance of the action with dependencies
        $nbnOrderService = app(\App\Services\NbnOrderService::class);
        $responder = app(ProcessNbnApplicationsResponder::class);
        $action = new ProcessNbnApplicationsAction($nbnOrderService, $responder);

        // Execute the action
        $response = $action();

        // Assert that the action processed the applications and dispatched the jobs
        $this->assertEquals(200, $response->getStatusCode());
        Queue::assertPushed(AutomateNbnOrderJob::class, $applications->count());
    }
}
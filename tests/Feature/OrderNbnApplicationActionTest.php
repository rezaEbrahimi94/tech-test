<?php

namespace Tests\Feature;

use App\Actions\OrderNbnApplicationAction;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Services\NbnOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Plan;

class OrderNbnApplicationActionTest extends TestCase
{
    use RefreshDatabase;

    private $action;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up the NbnOrderService instance
        $this->service = new NbnOrderService();

        // Set up the OrderNbnApplicationAction instance with the NbnOrderService instance
        $this->action = new OrderNbnApplicationAction($this->service);
    }

    public function testPlaceOrderWithSuccessfulResponse()
    {
        // Set the NBN_RESPONSE_FILE environment variable to the success response file
        putenv('NBN_RESPONSE_FILE=nbn-successful-response.json');

        // Create a dummy plan
        $plan = Plan::factory()->create([
            'name' => 'Sample Plan',
        ]);
        // Create a dummy application with required fields and associate it with the plan
        $application = Application::factory()->create([
            'address_1' => '123 Main St',
            'address_2' => 'Apt 4',
            'city' => 'New York',
            'state' => 'NY',
            'postcode' => '10001',
            'plan_id' => $plan->id,
        ]);
        // Call the __invoke method on the action
        $this->action->__invoke($application);

        // Reload the application from the database to get the latest changes
        $application->refresh();

        // Assert that the application status is updated to Complete
        $this->assertEquals(ApplicationStatus::Complete, $application->status);

        // Assert that the application's order_id is set
        $this->assertEquals('ORD000000000000', $application->order_id);
    }

    public function testPlaceOrderWithFailedResponse()
    {
        // Set the NBN_RESPONSE_FILE environment variable to the fail response file
        putenv('NBN_RESPONSE_FILE=nbn-fail-response.json');

        // Create a dummy plan
        $plan = Plan::factory()->create([
            'name' => 'Sample Plan',
        ]);
        // Create a dummy application with required fields and associate it with the plan
        $application = Application::factory()->create([
            'address_1' => '123 Main St',
            'address_2' => 'Apt 4',
            'city' => 'New York',
            'state' => 'NY',
            'postcode' => '10001',
            'plan_id' => $plan->id,
        ]);

        // Call the __invoke method on the action
        $this->action->__invoke($application);

        // Reload the application from the database to get the latest changes
        $application->refresh();

        // Assert that the application status is updated to OrderFailed
        $this->assertEquals(ApplicationStatus::OrderFailed, $application->status);

        // Assert that the application's order_id is not set
        $this->assertNull($application->order_id);
    }
}
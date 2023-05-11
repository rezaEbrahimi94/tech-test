<?php

namespace Tests\Feature\Actions;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Customer;
use App\Models\Plan;
use App\Services\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class ListApplicationsActionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test listing all applications.
     *
     * @return void
     */
    public function testListAllApplications()
    {
        // Create a customer and plan
        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create();
    
        // Create an application associated with the customer and plan
        Application::factory()->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
        ]);
    
        // Mock the application service to return a specific response
        $this->mock(ApplicationService::class, function ($mock) {
            // Create a LengthAwarePaginator instance with the data
            $paginator = new LengthAwarePaginator(
                collect([
                    [
                        'id' => 1,
                        'customer_full_name' => 'John Doe',
                        'address' => '123 Main St',
                        'plan_type' => 'nbn',
                        'plan_name' => 'NBN Plan',
                        'state' => 'VIC',
                        'plan_monthly_cost' => '49.99',
                    ],
                ]),
                1, // Total items
                10, // Items per page
                1 // Current page
            );
    
            $mock->shouldReceive('getAllApplications')
                ->once()
                ->with(null)
                ->andReturn($paginator);
        });
    
        // Send a GET request to the endpoint
        $response = $this->getJson('api/applications');
    
        // Assert that the response is successful
        $response->assertOk();
        // dd($response->decodeResponseJson());
    
        // Assert the response JSON structure and values
        $response->assertJson([
            'data' => [
                'headers' => [],
                'original' => [
                    'data' => [
                        'current_page' => 1,
                        'data' => [
                            [
                                'id' => 1,
                                'customer_full_name' => 'John Doe',
                                'address' => '123 Main St',
                                'plan_type' => 'nbn',
                                'plan_name' => 'NBN Plan',
                                'state' => 'VIC',
                                'plan_monthly_cost' => '49.99',
                            ],
                        ],
                        'first_page_url' => '/?page=1',
                        'from' => 1,
                        'last_page' => 1,
                        'last_page_url' => '/?page=1',
                        'links' => [
                            [
                                'url' => null,
                                'label' => '&laquo; Previous',
                                'active' => false,
                            ],
                            [
                                'url' => '/?page=1',
                                'label' => '1',
                                'active' => true,
                            ],
                            [
                                'url' => null,
                                'label' => 'Next &raquo;',
                                'active' => false,
                            ],
                        ],
                        'next_page_url' => null,
                        'path' => '/',
                        'per_page' => 10,
                        'prev_page_url' => null,
                        'to' => 1,
                        'total' => 1,
                    ],
                ],
                'exception' => null,
            ],
        ]);
    }
    /**
     * Test listing applications with plan type filter.
     *
     * @return void
     */
    public function testListApplicationsWithPlanTypeFilter()
    {
        // Create a customer and plan
        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create();

        // Create an application associated with the customer and plan
        Application::factory()->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
        ]);

        // Mock the application service to return a specific response
        $this->mock(ApplicationService::class, function ($mock) {
            // Create a LengthAwarePaginator instance with the data
            $paginator = new LengthAwarePaginator(
                collect([
                    [
                        'id' => 1,
                        'customer_full_name' => 'John Doe',
                        'address' => '123 Main St',
                        'plan_type' => 'nbn',
                        'plan_name' => 'NBN Plan',
                        'state' => 'VIC',
                        'plan_monthly_cost' => '49.99',
                    ],
                ]),
                1, // Total items
                10, // Items per page
                1 // Current page
            );
    
            $mock->shouldReceive('getAllApplications')
                ->once()
                ->with('nbn') // Pass the filter value
                ->andReturn($paginator);
        });
    
        // Send a GET request to the endpoint with the plan_type filter
        $response = $this->getJson('api/applications?plan_type=nbn');
    
        // Assert that the response is successful
        $response->assertOk();
    
        // Assert the response JSON structure and values
        $response->assertJson([
            'data' => [
                'headers' => [],
                'original' => [
                    'data' => [
                        'current_page' => 1,
                        'data' => [
                            [
                                'id' => 1,
                                'customer_full_name' => 'John Doe',
                                'address' => '123 Main St',
                                'plan_type' => 'nbn',
                                'plan_name' => 'NBN Plan',
                                'state' => 'VIC',
                                'plan_monthly_cost' => '49.99',
                            ],
                        ],
                        'first_page_url' => '/?page=1',
                        'from' => 1,
                        'last_page' => 1,
                        'last_page_url' => '/?page=1',
                        'links' => [
                            [
                                'url' => null,
                                'label' => '&laquo; Previous',
                                'active' => false,
                            ],
                            [
                                'url' => '/?page=1',
                                'label' => '1',
                                'active' => true,
                            ],
                            [
                                'url' => null,
                                'label' => 'Next &raquo;',
                                'active' => false,
                            ],
                        ],
                        'next_page_url' => null,
                        'path' => '/',
                        'per_page' => 10,
                        'prev_page_url' => null,
                        'to' => 1,
                        'total' => 1,
                    ],
                ],
                'exception' => null,
            ],
        ]);
    }

    /**
     * Test the condition when the order status is complete and order_id is present.
     *
     * @return void
     */
    public function testOrderStatusCompleteWithOrderId()
    {
        // Create a customer and plan
        $customer = Customer::factory()->create();
        $plan = Plan::factory()->create();
         // Create an application associated with the customer and plan
         Application::factory()->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => ApplicationStatus::Complete,
            'order_id' => 'ABC123',
        ]);

        // Mock the application service to return a specific response
        $this->mock(ApplicationService::class, function ($mock) {
            // Create a LengthAwarePaginator instance with the data
            $paginator = new LengthAwarePaginator(
                collect([
                    [
                        'id' => 1,
                        'customer_full_name' => 'John Doe',
                        'address' => '123 Main St',
                        'plan_type' => 'nbn',
                        'plan_name' => 'NBN Plan',
                        'state' => 'VIC',
                        'plan_monthly_cost' => '49.99',
                        'order_id' => 'ABC123',
                    ],
                ]),
                1, // Total items
                10, // Items per page
                1 // Current page
            );

            $mock->shouldReceive('getAllApplications')
                ->once()
                ->with(null)
                ->andReturn($paginator);
        });

        // Send a GET request to the endpoint
        $response = $this->getJson('api/applications');

        // Assert that the response is successful
        $response->assertOk();

        // Assert the response JSON structure and values
         // Assert the response JSON structure and values
         $response->assertJson([
            'data' => [
                'headers' => [],
                'original' => [
                    'data' => [
                        'current_page' => 1,
                        'data' => [
                            [
                                'id' => 1,
                                'customer_full_name' => 'John Doe',
                                'address' => '123 Main St',
                                'plan_type' => 'nbn',
                                'plan_name' => 'NBN Plan',
                                'state' => 'VIC',
                                'plan_monthly_cost' => '49.99',
                                'order_id' => 'ABC123',

                            ],
                        ],
                        'first_page_url' => '/?page=1',
                        'from' => 1,
                        'last_page' => 1,
                        'last_page_url' => '/?page=1',
                        'links' => [
                            [
                                'url' => null,
                                'label' => '&laquo; Previous',
                                'active' => false,
                            ],
                            [
                                'url' => '/?page=1',
                                'label' => '1',
                                'active' => true,
                            ],
                            [
                                'url' => null,
                                'label' => 'Next &raquo;',
                                'active' => false,
                            ],
                        ],
                        'next_page_url' => null,
                        'path' => '/',
                        'per_page' => 10,
                        'prev_page_url' => null,
                        'to' => 1,
                        'total' => 1,
                    ],
                ],
                'exception' => null,
            ],
        ]);
    
    }
}

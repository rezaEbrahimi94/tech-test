<?php


namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class NbnOrderService
{
    /**
     * Get all NBN applications eligible for ordering.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getApplicationsForOrder(): \Illuminate\Support\Collection
    {
        return Application::where('status', 'order')
            ->whereHas('plan', function ($query) {
                $query->where('type', 'nbn');
            })
            ->get();
    }

    /**
     * Place the NBN order for an application.
     *
     * @param Application $application
     * @return void
     * @throws \Exception
     */
    public function placeOrder(Application $application): void
    {
        // Determine the response file to use based on the environment variable
        $responseFile = env('NBN_RESPONSE_FILE', 'nbn-successful-response.json');

        // Read the response file contents
        $responseContents = file_get_contents(base_path('tests/stubs/' . $responseFile));

        // Parse the response JSON
        $response = json_decode($responseContents, true);

        $requestData = $this->validateRequestData($application);

        // Process the response based on the status
        if ($this->shouldUseRealRequest()) {
            $response = Http::post(env('NBN_B2B_ENDPOINT'), $requestData)->json();
        }

        if ($response['status'] === 'Successful') {
            $this->handleSuccessfulResponse($application, $response);
        } else {
            $this->handleFailedResponse($application, $response);
        }
    }

    /**
     * Validate the request data before sending the POST request.
     *
     * @param Application $application
     * @return array
     * @throws \Exception
     */
    private function validateRequestData(Application $application): array
    {
        $validator = Validator::make([
            'address_1' => $application->address_1,
            'address_2' => $application->address_2,
            'city' => $application->city,
            'state' => $application->state,
            'postcode' => $application->postcode,
            'plan_name' => $application->plan->name,
        ], [
            'address_1' => 'required|string',
            'address_2' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postcode' => 'required|string',
            'plan_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * Handle the successful response from the NBN API.
     *
     * @param Application $application
     * @param array $response
     * @return void
     */
    private function handleSuccessfulResponse(Application $application, array $response): void
    {
        // Process the successful response
        // Update the application status, store the Order Id, etc.
        // Progress the application to a complete status
        $application->order_id = $response['id'];
        $application->status = ApplicationStatus::Complete;
        $application->save();
    }

    /**
     * Handle the failed response from the NBN API.
     *
     * @param Application $application
     * @param array $response
     * @return void
     */
    private function handleFailedResponse(Application $application, array $response): void
    {
        // Process the failed response
        // Update the application status to indicate the failure
        $application->status = ApplicationStatus::OrderFailed;
        $application->save();
    }

    /**
     * Check if the real request should be used.
     *
     * @return bool
     */
    private function shouldUseRealRequest(): bool
    {
        // Determine if the real request should be used based on environment or other conditions
        return env('USE_REAL_REQUEST', false);
    }
}

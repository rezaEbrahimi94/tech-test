<?php
namespace App\Actions;

use App\Services\NbnOrderService;
use App\Responders\ProcessNbnApplicationsResponder;
use App\Jobs\AutomateNbnOrderJob;

class ProcessNbnApplicationsAction
{
    protected $nbnOrderService;
    protected $responder;

    /**
     * Create a new action instance.
     *
     * @param NbnOrderService $nbnOrderService
     * @param ProcessNbnApplicationsResponder $responder
     */
    public function __construct(NbnOrderService $nbnOrderService, ProcessNbnApplicationsResponder $responder)
    {
        $this->nbnOrderService = $nbnOrderService;
        $this->responder = $responder;
    }

    /**
     * Process NBN applications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke()
    {
        try {
            // Get all NBN applications eligible for ordering
            $applications = $this->nbnOrderService->getApplicationsForOrder();
            foreach ($applications as $application) {
                // Dispatch the AutomateNbnOrderJob to process each application in a queue
                AutomateNbnOrderJob::dispatch($application);
            }
            return $this->responder->successResponse();
        } catch (\Exception $e) {
            return $this->responder->errorResponse($e->getMessage());
        }
    }
}
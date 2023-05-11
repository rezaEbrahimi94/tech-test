<?php

namespace App\Actions;

use App\Services\ApplicationService;
use App\Http\Requests\ListApplicationsRequest;
use App\Responders\ListApplicationsResponder;

class ListApplicationsAction
{
    /**
     * The application service instance.
     *
     * @var ApplicationService
     */
    protected $applicationService;

    /**
     * The list applications responder instance.
     *
     * @var ListApplicationsResponder
     */
    protected $responder;

    /**
     * Create a new ListApplicationsAction instance.
     *
     * @param ApplicationService $applicationService
     * @param ListApplicationsResponder $responder
     */
    public function __construct(ApplicationService $applicationService, ListApplicationsResponder $responder)
    {
        $this->applicationService = $applicationService;
        $this->responder = $responder;
    }

    /**
     * Handle the list applications action.
     *
     * @param ListApplicationsRequest $request
     * @return mixed
     */
    public function __invoke(ListApplicationsRequest $request)
    {
        $planType = $request->input('plan_type');
        $applications = $this->applicationService->getAllApplications($planType);

        return $this->responder->respond($applications);
    }
}

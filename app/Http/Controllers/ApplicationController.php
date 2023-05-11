<?php


namespace App\Http\Controllers;

use App\Actions\ListApplicationsAction;
use App\Responders\ListApplicationsResponder;
use App\Http\Requests\ListApplicationsRequest;
use Illuminate\Http\JsonResponse;

class ApplicationController extends Controller
{
    /**
     * Get a list of applications.
     *
     * @param  ListApplicationsRequest     $request
     * @param  ListApplicationsAction      $listApplicationsAction
     * @param  ListApplicationsResponder   $listApplicationsResponder
     * @return JsonResponse
     */
    public function index(
        ListApplicationsRequest $request,
        ListApplicationsAction $listApplicationsAction,
        ListApplicationsResponder $listApplicationsResponder
    ): JsonResponse {
        $applications = $listApplicationsAction($request);

        return $listApplicationsResponder->respond($applications);
    }
}
<?php

namespace App\Responders;

use Illuminate\Http\JsonResponse;

class ProcessNbnApplicationsResponder
{
    /**
     * Generate a success response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse(): JsonResponse
    {
        return response()->json(['message' => 'NBN applications processed successfully']);
    }

    /**
     * Generate an error response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse(string $message): JsonResponse
    {
        return response()->json(['error' => $message], 500);
    }
}
<?php

namespace App\Responders;

use Illuminate\Http\JsonResponse;

class ListApplicationsResponder
{
    public function respond($data)
    {
        return new JsonResponse([
            'data' => $data,
        ]);
    }
}
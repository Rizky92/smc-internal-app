<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\PostErrorRequest;
use Illuminate\Http\JsonResponse;

class KhanzaPostErrorController
{
    public function __invoke(PostErrorRequest $reqeust): JsonResponse
    {
        return response()->json();
    }
}

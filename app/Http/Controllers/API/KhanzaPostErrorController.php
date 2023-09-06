<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\PostErrorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class KhanzaPostErrorController
{
    public function __invoke(PostErrorRequest $request): JsonResponse
    {
        $data = new Fluent($request->validated());

        Log::channel('khanza')
            ->error($data->message, $data->stack);

        return response()->json();
    }
}

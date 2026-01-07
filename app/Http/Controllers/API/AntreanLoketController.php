<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\AntreanLoketRequest;
use App\Services\AntreanLoketService;
use Illuminate\Http\JsonResponse;

class AntreanLoketController
{
    protected $antreanLoketService;

    public function __construct(
        AntreanLoketService $antreanLoketService
    ) {
        $this->antreanLoketService = $antreanLoketService;
    }

    public function call(AntreanLoketRequest $request): JsonResponse
    {
        $data = $request->validated();

        $this->antreanLoketService->call($data);

        return response()->json($data, 200);
    }

    public function stop(AntreanLoketRequest $request): JsonResponse
    {
        $request->validated();

        $this->antreanLoketService->stop();

        return response()->json(['message' => 'Antrean loket stopped successfully.'], 200);
    }
}

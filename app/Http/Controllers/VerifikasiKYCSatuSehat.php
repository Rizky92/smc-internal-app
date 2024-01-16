<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifikasiKYCSatuSehatRequest;
use Illuminate\Http\RedirectResponse;

class VerifikasiKYCSatuSehat
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(VerifikasiKYCSatuSehatRequest $request): RedirectResponse
    {
        $input = $request->validated();

        
    }
}

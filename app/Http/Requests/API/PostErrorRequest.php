<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class PostErrorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message'   => ['required', 'string'],
            'timestamp' => ['required', 'string'],
            'stack'     => ['required', 'array'],
            'stack.*'   => ['string'],
        ];
    }
}

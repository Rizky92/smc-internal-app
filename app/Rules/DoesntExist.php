<?php

namespace App\Rules;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use Illuminate\Contracts\Validation\Rule;

class DoesntExist implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return ! AnggaranBidang::whereTahun($value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Tahun RKAT sudah ada anggaran yang digunakan di tahun tersebut!';
    }
}

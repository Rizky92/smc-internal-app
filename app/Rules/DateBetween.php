<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class DateBetween implements Rule
{
    private Carbon $startDate;

    private Carbon $endDate;

    /**
     * Create a new rule instance.
     *
     * @param  Carbon|\DateTime|string  $start
     * @param  Carbon|\DateTime|string  $end
     * @return void
     */
    public function __construct($start, $end)
    {
        $this->startDate = carbon($start);
        $this->endDate = carbon($end);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Carbon|\DateTime|string|mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value instanceof Carbon) {
            return $value->between($this->startDate, $this->endDate);
        }

        if ($value instanceof \DateTime || is_string($value)) {
            return carbon($value)->between($this->startDate, $this->endDate);
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return str(':Attribute harus sesuai periode antara :start hingga :end')
            ->replace(':start', $this->startDate->toDateString())
            ->replace(':end', $this->endDate->toDateString())
            ->value();
    }
}

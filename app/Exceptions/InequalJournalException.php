<?php

namespace App\Exceptions;

use RuntimeException;

class InequalJournalException extends RuntimeException
{
    /**
     * @psalm-param numeric $debit
     * @psalm-param numeric $credit
     */
    public function __construct($debit, $credit)
    {
        $message = str(collect(['debit' => $debit, 'credit' => $credit])->toJson())
            ->prepend('Debit and credit must be equal. ')
            ->value();

        parent::__construct($message);
    }
}

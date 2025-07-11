<?php

namespace App\Exceptions;

use Exception;

class TransactionLessThanZeroException extends Exception
{
    /**
     * @psalm-param numeric $debit
     * @psalm-param numeric $credit
     */
    public function __construct($debit, $credit)
    {
        $message = str(collect(['debit' => $debit, 'credit' => $credit])->toJson())
            ->prepend('Less than zero transaction occured during processing. ')
            ->value();

        parent::__construct($message);
    }
}

<?php

namespace App\Exceptions;

use Exception;

class EmptyTransactionException extends Exception
{
    /**
     * @psalm-param numeric $debit
     * @psalm-param numeric $credit
     */
    public function __construct($debit, $credit)
    {
        $message = str(collect(['debit' => $debit, 'credit' => $credit])->toJson())
            ->prepend('Empty journal transaction occured during processing. ')
            ->value();

        parent::__construct($message);
    }
}

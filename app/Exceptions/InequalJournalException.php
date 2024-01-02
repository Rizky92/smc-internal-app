<?php

namespace App\Exceptions;

use RuntimeException;

class InequalJournalException extends RuntimeException
{
    /**
     * @psalm-param numeric $debit
     * @psalm-param numeric $credit
     */
    public function __construct($debit, $credit, string $journalNo)
    {
        $message = [
            'journalNo' => $journalNo,
            'debit'     => $debit,
            'credit'    => $credit,
        ];

        $this->message = str(collect($message)->toJson())
            ->prepend('Debit and credit must be equal. ')
            ->value();

        parent::__construct($this->message);
    }
}

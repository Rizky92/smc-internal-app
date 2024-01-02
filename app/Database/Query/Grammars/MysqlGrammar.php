<?php

namespace App\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\MySqlGrammar as BaseMysqlGrammar;

class MysqlGrammar extends BaseMysqlGrammar
{
    /**
     * {@inheritdoc}
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s.u';
    }
}

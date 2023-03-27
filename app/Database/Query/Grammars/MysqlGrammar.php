<?php

namespace App\Database\Query\Grammars;

class MysqlGrammar extends \Illuminate\Database\Query\Grammars\MySqlGrammar
{
    /**
     * @inheritdoc
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }
}
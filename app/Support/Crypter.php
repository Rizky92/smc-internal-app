<?php

namespace App\Support;

use BadMethodCallException;
use NoProtocol\Encryption\MySQL\AES\Crypter as BaseCrypter;
use RuntimeException;

class Crypter extends BaseCrypter
{
    // /**
    //  * @psalm-suppress MissingParamType
    //  */
    // public static function __callStatic($name, $arguments)
    // {
    //     $passthru = ['encrypt', 'decrypt', 'generateKey'];

    //     if (empty(static::$seed)) {
    //         throw new RuntimeException("Seed is not defined");
    //     }

    //     if (! in_array($name, $passthru, true)) {
    //         throw new BadMethodCallException;
    //     }

    //     return (new static(static::$seed))->$name(...$arguments);
    // }
}
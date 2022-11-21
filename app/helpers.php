<?php

if (! function_exists('rp')) {
    function rp($nominal, $decimalCount = 2) {
        return 'Rp. ' . number_format($nominal, $decimalCount, ',', '.');
    }
}
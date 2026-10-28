<?php

// app/Support/MoneyParser.php
namespace App\Support;

use InvalidArgumentException;

class MoneyParser
{
    public static function toInt(string|int $v): int
    {
        if (is_int($v)) {
            return self::guard($v);
        }

        $s = strtolower(trim($v));
        $s = preg_replace('/^rp\.?\s*/', '', $s);
        $mult = 1;

        if (preg_match('/\s*(jt|juta)$/', $s)) {
            $mult = 1_000_000;
            $s = preg_replace('/\s*(jt|juta)$/', '', $s);
        } elseif (preg_match('/\s*(rb|ribu|k)$/', $s)) {
            $mult = 1_000;
            $s = preg_replace('/\s*(rb|ribu|k)$/', '', $s);
        }

        if ($mult > 1) {
            $s = str_replace(',', '.', $s);          // "1,5jt" -> 1.5
            if (! is_numeric($s)) {
                throw new InvalidArgumentException("Nominal tidak valid: $v");
            }
            return self::guard((int) round((float) $s * $mult));
        }

        $digits = preg_replace('/\D/', '', $s);       // "25.000" -> 25000
        if ($digits === '') {
            throw new InvalidArgumentException("Nominal tidak valid: $v");
        }
        return self::guard((int) $digits);
    }

    private static function guard(int $n): int
    {
        if ($n <= 0) {
            throw new InvalidArgumentException('Nominal harus lebih dari 0');
        }
        return $n;
    }
}
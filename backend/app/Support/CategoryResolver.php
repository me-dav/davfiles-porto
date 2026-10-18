<?php

// app/Support/CategoryResolver.php
namespace App\Support;

use App\Models\FinanceCategory;

class CategoryResolver
{
    public static function find(string $name, string $type): ?FinanceCategory
    {
        $name = trim($name);
        return FinanceCategory::where('kind', $type)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
    }
}
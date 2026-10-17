<?php

use App\Support\MoneyParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyParserTest extends TestCase
{
    #[DataProvider('cases')]
    public function test_parse(string|int $in, int $out): void
    {
        $this->assertSame($out, MoneyParser::toInt($in));
    }

    public static function cases(): array
    {
        return [
            ['25rb', 25000],
            ['25k', 25000],
            ['1,5jt', 1500000],
            ['1.5jt', 1500000],
            ['2jt', 2000000],
            ['25.000', 25000],
            ['Rp 25.000', 25000],
            ['25000', 25000],
            [25000, 25000],
            ['Rp. 1.250.000', 1250000],
        ];
    }

    public function test_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MoneyParser::toInt('abc');
    }
}

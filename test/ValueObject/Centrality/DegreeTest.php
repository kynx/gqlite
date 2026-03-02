<?php

declare(strict_types=1);

namespace KynxTest\GqLite\ValueObject\Centrality;

use Kynx\GqLite\ValueObject\Centrality\Degree;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Degree::class)]
final class DegreeTest extends TestCase
{
    public function testFromArrayReturnsInstance(): void
    {
        $expected = new Degree('A1', 10, 3, 1);
        $actual   = Degree::fromArray(['user_id' => 'A1', 'degree' => 1, 'out_degree' => 3, 'in_degree' => 10]);
        self::assertTrue($expected->equals($actual));
    }
}

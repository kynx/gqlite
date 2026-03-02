<?php

declare(strict_types=1);

namespace KynxTest\GqLite\ValueObject\Centrality;

use Kynx\GqLite\ValueObject\Centrality\Score;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Score::class)]
final class ScoreTest extends TestCase
{
    public function testFromArrayReturnsInstance(): void
    {
        $expected = new Score('A1', 0.666);
        $actual   = Score::fromArray(['user_id' => 'A1', 'score' => 0.666]);
        self::assertTrue($expected->equals($actual));
    }
}

<?php

declare(strict_types=1);

namespace Kynx\GqLite\ValueObject\Centrality;

/**
 * @phpstan-type CentralityScoreRow = array{user_id: string, score: float}
 */
final readonly class Score
{
    public function __construct(public string $nodeId, public float $score)
    {
    }

    /**
     * @param CentralityScoreRow $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['user_id'], $data['score']);
    }

    public function equals(mixed $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->nodeId === $other->nodeId && $this->score === $other->score;
    }
}

<?php

declare(strict_types=1);

namespace Kynx\GqLite\ValueObject\Centrality;

use function get_object_vars;

/**
 * @phpstan-type CentralityDegreeRow = array{user_id: string, in_degree: int, out_degree: int, degree: int}
 */
final readonly class Degree
{
    public function __construct(public string $nodeId, public int $inDegree, public int $outDegree, public int $degree)
    {
    }

    /**
     * @param CentralityDegreeRow $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['user_id'], $data['in_degree'], $data['out_degree'], $data['degree']);
    }

    public function equals(mixed $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return get_object_vars($this) === get_object_vars($other);
    }
}

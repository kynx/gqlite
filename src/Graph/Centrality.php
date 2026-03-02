<?php

declare(strict_types=1);

namespace Kynx\GqLite\Graph;

use Kynx\GqLite\ConnectionInterface;
use Kynx\GqLite\Cypher\Result;
use Kynx\GqLite\ValueObject\Centrality\Degree;
use Kynx\GqLite\ValueObject\Centrality\Score;

use function sprintf;

/**
 * @phpstan-import-type CentralityScoreRow from Score
 * @phpstan-import-type CentralityDegreeRow from Degree
 */
final readonly class Centrality implements CentralityInterface
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function pageRank(float $damping = 0.85, int $iterations = 20): array
    {
        /** @var Result<array{column_0: CentralityScoreRow}> $results */
        $results = $this->connection->cypher(
            sprintf('RETURN pageRank(%f, %d)', $damping, $iterations)
        );

        return $this->mapCentralityScores($results);
    }

    public function topPageRank(int $limit, float $damping = 0.85, int $iterations = 20): array
    {
        /** @var Result<array{column_0: CentralityScoreRow}> $results */
        $results = $this->connection->cypher(
            sprintf('RETURN topPageRank(%d, %f, %d)', $limit, $damping, $iterations)
        );

        return $this->mapCentralityScores($results);
    }

    public function degreeCentrality(): array
    {
        /** @var Result<array{column_0: CentralityDegreeRow}> $results */
        $results = $this->connection->cypher('RETURN degreeCentrality()');

        $degrees = [];
        $current = $results->current();
        foreach ($current['column_0'] as $row) {
            if (! isset($row['user_id'])) {
                continue;
            }

            $degrees[] = Degree::fromArray($row);
        }

        return $degrees;
    }

    public function betweennessCentrality(): array
    {
        /** @var Result<array{column_0: CentralityScoreRow}> $results */
        $results = $this->connection->cypher('RETURN betweennessCentrality()');

        return $this->mapCentralityScores($results);
    }

    public function closenessCentrality(): array
    {
        /** @var Result<array{column_0: CentralityScoreRow}> $results */
        $results = $this->connection->cypher('RETURN closenessCentrality()');

        return $this->mapCentralityScores($results);
    }

    public function eigenvectorCentrality(int $iterations = 100): array
    {
        /** @var Result<array{column_0: CentralityScoreRow}> $results */
        $results = $this->connection->cypher(
            sprintf('RETURN eigenvectorCentrality(%d)', $iterations)
        );

        return $this->mapCentralityScores($results);
    }

    /**
     * @param Result<array{column_0: CentralityScoreRow}> $results
     * @return list<Score>
     */
    private function mapCentralityScores(Result $results): array
    {
        $scores  = [];
        $current = $results->current();
        foreach ($current['column_0'] as $row) {
            if (! isset($row['user_id'])) {
                continue;
            }

            $scores[] = Score::fromArray($row);
        }

        return $scores;
    }
}

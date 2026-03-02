<?php

declare(strict_types=1);

namespace Kynx\GqLite\Graph;

use Kynx\GqLite\ValueObject\Centrality\Degree;
use Kynx\GqLite\ValueObject\Centrality\Score;

interface CentralityInterface
{
    /**
     * Returns results of PageRank algorithm, ordered from highest to lowest score
     *
     * @return list<Score>
     */
    public function pageRank(float $damping = 0.85, int $iterations = 20): array;

    /**
     * Returns top `$limit` results of PageRank algorithm, ordered from highest to lowest score
     *
     * @return list<Score>
     */
    public function topPageRank(int $limit, float $damping = 0.85, int $iterations = 20): array;

    /**
     * Returns the in-degree, out-degree, and total degree for each node
     *
     * @return list<Degree>
     */
    public function degree(): array;

    /**
     * Returns betweenness centrality for all nodes
     *
     * Betweenness centrality measures how often a node lies on shortest
     * paths between other nodes. Uses Brandes' algorithm for O(VE) complexity.
     *
     * @return list<Score>
     */
    public function betweenness(): array;

    /**
     * Returns closeness centrality for all nodes
     *
     * Closeness centrality measures how close a node is to all other nodes
     * based on average shortest path length. Uses harmonic centrality variant
     * to handle disconnected graphs. O(V * (V + E)) complexity.
     *
     * @return list<Score>
     */
    public function closeness(): array;

    /**
     * Returns eigenvector centrality for all nodes
     *
     * Eigenvector centrality measures node importance based on connections
     * to other important nodes. Uses power iteration method.
     *
     * Unlike PageRank, eigenvector centrality has no damping factor and
     * simply measures influence based on neighbor centrality scores.
     *
     * @return list<Score>
     */
    public function eigenvector(int $iterations = 100): array;
}

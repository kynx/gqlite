<?php

declare(strict_types=1);

namespace KynxTest\GqLite\Graph;

use Kynx\GqLite\Graph\Centrality;
use Kynx\GqLite\Graph\Edges;
use Kynx\GqLite\Graph\Nodes;
use Kynx\GqLite\ValueObject\Centrality\Degree;
use Kynx\GqLite\ValueObject\Centrality\Score;
use Kynx\GqLite\ValueObject\Edge;
use Kynx\GqLite\ValueObject\Node;
use KynxTest\GqLite\ConnectionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_map;

#[CoversClass(Centrality::class)]
final class CentralityTest extends TestCase
{
    use ConnectionTrait;

    private Nodes $nodes;
    private Edges $edges;
    private Centrality $algorithms;

    protected function setUp(): void
    {
        parent::setUp();

        $connection       = $this->getConnection();
        $this->nodes      = new Nodes($connection);
        $this->edges      = new Edges($connection);
        $this->algorithms = new Centrality($connection);
    }

    public function testPageRankWithEmptyGraph(): void
    {
        $actual = $this->algorithms->pageRank();
        self::assertSame([], $actual);
    }

    public function testPageRankReturnsScores(): void
    {
        $expected = ['pr3', 'pr1', 'pr2', 'pr4'];

        $this->createPageRankGraph();

        $scores = $this->algorithms->pageRank(0.85, 50);
        self::assertCount(4, $scores);
        self::assertGreaterThan(0.33, $scores[0]->score);
        self::assertLessThan(0.1, $scores[3]->score);

        $actual = array_map(static fn (Score $score): string => $score->nodeId, $scores);
        self::assertEquals($expected, $actual);
    }

    public function testTopPageRankWithEmptyGraph(): void
    {
        $actual = $this->algorithms->topPageRank(10);
        self::assertSame([], $actual);
    }

    public function testTopPageRankLimitsScores(): void
    {
        $expected = ['pr3', 'pr1'];

        $this->createPageRankGraph();

        $scores = $this->algorithms->topPageRank(2, 0.85, 50);
        self::assertCount(2, $scores);
        self::assertGreaterThan(0.33, $scores[0]->score);

        $actual = array_map(static fn (Score $score): string => $score->nodeId, $scores);
        self::assertEquals($expected, $actual);
    }

    public function testDegreeWithEmptyGraph(): void
    {
        $actual = $this->algorithms->degree();
        self::assertSame([], $actual);
    }

    public function testDegreeReturnsDegrees(): void
    {
        $expected = [
            new Degree('dc1', 0, 2, 2),
            new Degree('dc2', 1, 1, 2),
            new Degree('dc3', 2, 0, 2),
        ];
        $this->nodes->upsert(new Node('dc1', ['name' => 'DC1'], 'Test'));
        $this->nodes->upsert(new Node('dc2', ['name' => 'DC2'], 'Test'));
        $this->nodes->upsert(new Node('dc3', ['name' => 'DC3'], 'Test'));
        $this->edges->upsert(new Edge('dc1', 'dc2'));
        $this->edges->upsert(new Edge('dc1', 'dc3'));
        $this->edges->upsert(new Edge('dc2', 'dc3'));

        $actual = $this->algorithms->degree();
        self::assertEquals($expected, $actual);
    }

    public function testBetweennessWithEmptyGraph(): void
    {
        $actual = $this->algorithms->betweenness();
        self::assertSame([], $actual);
    }

    public function testBetweennessReturnsScores(): void
    {
        $expected = [
            new Score('b1', 0.0),
            new Score('b2', 1.0),
            new Score('b3', 0.0),
        ];
        $this->nodes->upsert(new Node('b1', [], 'Test'));
        $this->nodes->upsert(new Node('b2', [], 'Test'));
        $this->nodes->upsert(new Node('b3', [], 'Test'));
        $this->edges->upsert(new Edge('b1', 'b2'));
        $this->edges->upsert(new Edge('b2', 'b3'));

        $actual = $this->algorithms->betweenness();
        self::assertEquals($expected, $actual);
    }

    public function testClosenessWithEmptyGraph(): void
    {
        $actual = $this->algorithms->closeness();
        self::assertSame([], $actual);
    }

    public function testClosenessReturnsScores(): void
    {
        $expected = [
            new Score('ch', 1.0),
            new Score('c1', 0.625),
            new Score('c2', 0.625),
            new Score('c3', 0.625),
            new Score('c4', 0.625),
        ];

        /**
         * Create star graph, with 4 nodes connected to central hub
         */
        $this->nodes->upsert(new Node('ch', [], 'Test'));
        $this->nodes->upsert(new Node('c1', [], 'Test'));
        $this->nodes->upsert(new Node('c2', [], 'Test'));
        $this->nodes->upsert(new Node('c3', [], 'Test'));
        $this->nodes->upsert(new Node('c4', [], 'Test'));
        $this->edges->upsert(new Edge('c1', 'ch'));
        $this->edges->upsert(new Edge('c2', 'ch'));
        $this->edges->upsert(new Edge('c3', 'ch'));
        $this->edges->upsert(new Edge('c4', 'ch'));

        $actual = $this->algorithms->closeness();
        self::assertEquals($expected, $actual);
    }

    public function testEigenvectorWithEmptyGraph(): void
    {
        $actual = $this->algorithms->eigenvector();
        self::assertSame([], $actual);
    }

    public function testEigenvectorReturnsScores(): void
    {
        $expected = [
            new Score('e3', 1.0),
            new Score('e1', 0.0),
            new Score('e2', 0.0),
        ];

        $this->nodes->upsert(new Node('e1', [], 'Test'));
        $this->nodes->upsert(new Node('e2', [], 'Test'));
        $this->nodes->upsert(new Node('e3', [], 'Test'));
        $this->edges->upsert(new Edge('e1', 'e2'));
        $this->edges->upsert(new Edge('e2', 'e3'));

        $actual = $this->algorithms->eigenvector(50);
        self::assertEquals($expected, $actual);
    }

    /**
     * Adapted from test_executor_pagerank.c
     *
     * Create a web-like graph:
     *   pr1 -> pr2, pr1 -> pr3
     *   pr2 -> pr3
     *   pr3 -> pr1
     *   pr4 -> pr3 (pr4 is dangling - only outgoing)
     *
     * Expected PageRank order: pr3 > pr1 > pr2 > pr4
     */
    private function createPageRankGraph(): void
    {
        $this->nodes->upsert(new Node('pr1', [], 'Test'));
        $this->nodes->upsert(new Node('pr2', [], 'Test'));
        $this->nodes->upsert(new Node('pr3', [], 'Test'));
        $this->nodes->upsert(new Node('pr4', [], 'Test'));
        $this->edges->upsert(new Edge('pr1', 'pr2'));
        $this->edges->upsert(new Edge('pr1', 'pr3'));
        $this->edges->upsert(new Edge('pr2', 'pr3'));
        $this->edges->upsert(new Edge('pr3', 'pr1'));
        $this->edges->upsert(new Edge('pr4', 'pr3'));
    }
}

<?php declare(strict_types=1);

use Biera\Filter\Binding\PDO\QueryParametersExtractor;
use Biera\Filter\Operator;
use PHPUnit\Framework\TestCase;

class QueryParametersExtractorTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function itExtractsPlaceholdersAndValues(Operator $operator, array $expectedPlaceholders, $expectedValues): void
    {
        $this->assertEquals(
            $expectedPlaceholders,
            QueryParametersExtractor::extractPlaceholders($operator)
        );

        $this->assertEquals(
            $expectedValues,
            QueryParametersExtractor::extractValues($operator)
        );
    }

    public function dataProvider(): array
    {
        return [
            [
                Operator::null('genre'),
                [],
                []
            ],
            [
                Operator::eq('genre', 'adventure'),
                [':genre'],
                [':genre' => 'adventure']
            ],
            [
                Operator::in('genre', ['adventure', 'crime']),
                [':genre__0', ':genre__1'],
                [
                    ':genre__0' => 'adventure',
                    ':genre__1' => 'crime'
                ]
            ],
            [
                Operator::in('genre.name', ['adventure', 'crime']),
                [':genre_name__0', ':genre_name__1'],
                [
                    ':genre_name__0' => 'adventure',
                    ':genre_name__1' => 'crime'
                ]
            ],
            [
                Operator::and(
                    Operator::gt('release_date', new DateTime('2000-01-01')),
                    Operator::lt('duration', 120),
                    Operator::in('genre', ['adventure', 'crime']),
                ),
                [':release__date', ':duration', ':genre__0', ':genre__1'],
                [
                    ':release__date' => '2000-01-01T00:00:00+0000',
                    ':duration' => 120,
                    ':genre__0' => 'adventure',
                    ':genre__1' => 'crime'
                ]
            ]
        ];
    }
}

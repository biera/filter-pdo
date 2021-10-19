<?php declare(strict_types=1);

use Biera\Filter\Binding\PDO\QueryParametersExtractor;
use Biera\Filter\Binding\PDO\StatementFactory;
use Biera\Filter\Operator;
use Biera\Filter\Test\FilterableCollection;

class MovieRepository implements FilterableCollection
{
    private StatementFactory $statementFactory;

    public function __construct(PDO $connection)
    {
        $query = <<<SQL
            SELECT movies.title 
            FROM movies
            INNER JOIN movies_actors ON movies.id = movies_actors.movie_id
            INNER JOIN persons as actor ON movies_actors.actor_id = actor.id
            INNER JOIN persons as director ON movies.director_id = director.id    
            INNER JOIN movies_genres ON movies.id = movies_genres.movie_id
            INNER JOIN genres as genre ON movies_genres.genre_id = genre.id
            %s
            GROUP BY movies.id
            ORDER BY movies.title ASC;
        SQL;

        $this->statementFactory = new StatementFactory($connection, $query);
    }

    public function findByFilters(Operator $expression): array
    {
        $statement = $this->statementFactory->create($expression);
        $statement->execute(
            QueryParametersExtractor::extractValues(
                $expression
            )
        );

        return array_map(
            fn (array $record) => $record['title'],
            $statement->fetchAll()
        );
    }
}

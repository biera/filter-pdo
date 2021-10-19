<?php declare(strict_types=1);

namespace Biera\Filter\Binding\PDO;

use Biera\Filter\Operator;
use PDO, PDOStatement;

class StatementFactory
{
    private PDO $connection;
    private string $query;

    /**
     * @param PDO $connection
     * @param string $query sql query with WHERE placeholder clause, eg: "SELECT * FROM table %s ORDER BY name GROUP BY id"
     */
    public function __construct(PDO $connection, string $query)
    {
        $this->connection = $connection;
        $this->query = $query;
    }

    public function create(Operator $filterExpression): PDOStatement
    {
        return $this->connection->prepare(
            sprintf($this->query, "WHERE {$this->createWhereClause($filterExpression)}")
        );
    }

    private function createWhereClause(Operator $filterExpression): string
    {
        $operatorType = $filterExpression->type();

        switch ($operatorType) {
            case Operator::AND:
            case Operator::OR:
                return array_reduce(
                    $filterExpression->operands(),
                    fn (?string $joined, Operator $operand) => is_null($joined)
                        ? $this->createWhereClause($operand)
                        : sprintf(
                            '%s %s %s',
                            $joined,
                            $operatorType,
                            $this->createWhereClause($operand)
                        ),
                    null
                );

            case Operator::NULL:
            case Operator::NOT_NULL:
                return sprintf(
                    '%s is %s',
                    $filterExpression->identifier(),
                    $this->mapOperator($operatorType)
                );

            default:
                $placeholders = QueryParametersExtractor::extractPlaceholders($filterExpression);

                return sprintf(
                    '%s %s (%s)',
                    $filterExpression->identifier(),
                    $this->mapOperator($operatorType),
                    join(',', $placeholders)
                );
        }
    }

    private function mapOperator(string $operator): string
    {
        switch ($operator) {
            case Operator::LT:
                return '<';
            case Operator::LTE:
                return '<=';
            case Operator::GT:
                return '>';
            case Operator::GTE:
                return '>=';
            case Operator::EQ:
                return '=';
            case Operator::NEQ:
                return '!=';
            case Operator::NULL:
                return 'null';
            case Operator::NOT_NULL:
                return 'not null';
            default:
                return $operator;
        }
    }
}

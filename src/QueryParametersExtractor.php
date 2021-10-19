<?php declare(strict_types=1);

namespace Biera\Filter\Binding\PDO;

use Biera\Filter\Operator;
use DateTimeInterface;

class QueryParametersExtractor
{
    public static function extractValues(Operator $filterExpression, array $params = []): array
    {
        $operatorType = $filterExpression->type();

        switch ($operatorType) {
            case Operator::AND:
            case Operator::OR:
                return array_merge(
                    ...array_map(
                        fn (Operator $filterExpression) => self::extractValues($filterExpression, $params),
                        $filterExpression->operands()
                    )
                );

            case Operator::LT:
            case Operator::LTE:
            case Operator::GT:
            case Operator::GTE:
            case Operator::EQ:
            case Operator::NEQ:
            case Operator::LIKE:
            case Operator::IN:
                $literal = $filterExpression->literal();
                $values = array_map(
                    fn ($literal) => self::formatLiteral($literal),
                    is_array($literal) ? $literal : [$literal]
                );

                return array_merge(
                    array_combine(
                        self::extractPlaceholders($filterExpression),
                        $values
                    ),
                    $params
                );

            default:
                return $params;
        }
    }

    // TODO should placeholder names be unique?
    public static function extractPlaceholders(Operator $operator, array $placeholders = []): array
    {
        switch ($operator->type())
        {
            case Operator::AND:
            case Operator::OR:
                return array_merge(
                    ...array_map(
                        fn (Operator $filterExpression)
                            => self::extractPlaceholders($filterExpression, $placeholders),
                        $operator->operands()
                    )
                );

            case Operator::LT:
            case Operator::LTE:
            case Operator::GT:
            case Operator::GTE:
            case Operator::EQ:
            case Operator::NEQ:
            case Operator::LIKE:
                $identifier = $operator->identifier();

                return array_merge(
                    [self::createPlaceholder($identifier)],
                    $placeholders
                );

            case Operator::IN:
                $identifier = $operator->identifier();
                $literalCounts = count($operator->literal());

                return array_merge(
                    array_map(
                        fn (string $identifier, int $index) => self::createPlaceholder("{$identifier}_{$index}"),
                        array_fill(0, $literalCounts, $identifier),
                        range(0, $literalCounts - 1)
                    ),
                    $placeholders
                );

            default:
                return [];
        }
    }

    private static function formatLiteral($literal)
    {
        return $literal instanceof DateTimeInterface
            ? $literal->format(DateTimeInterface::ISO8601)
            : $literal;
    }

    private static function createPlaceholder(string $identifier): string
    {
        return sprintf(':%s', self::escapePlaceholder($identifier));
    }

    private static function escapePlaceholder(string $identifier): string
    {
        return str_replace(['_', '.'], ['__', '_'], $identifier);
    }
}

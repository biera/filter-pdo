<?php declare(strict_types=1);

use Biera\Filter\Test\FilterableCollection;
use Biera\Filter\Test\Suite;

require_once __DIR__ . '/MovieRepository.php';

class FilterableCollectionTest extends Suite
{
    private const DATABASE_PATH = '/tmp/movies-db.sqlite3';

    private static FilterableCollection $filterableCollection;

    public function getFilterableCollection(): FilterableCollection
    {
        return self::$filterableCollection;
    }

    public static function setUpBeforeClass(): void
    {
        $connection = new PDO("sqlite:/" . self::DATABASE_PATH);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        self::loadSQLSchemaAndData($connection);
        self::$filterableCollection = new MovieRepository($connection);
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::DATABASE_PATH)) {
            unlink(self::DATABASE_PATH);
        }
    }
}
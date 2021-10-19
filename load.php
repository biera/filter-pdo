<?php declare(strict_types=1);

function loadIntoSqlLite(string $databasePath, string $filename): void
{
    passthru("cat {$filename} | sqlite3 {$databasePath}");
}

loadIntoSqlLite('/tmp/db.sqlite3', __DIR__ . '/../filter/tests/resources/dump.sql');

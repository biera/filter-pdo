<?php declare(strict_types=1);

$query = <<<SQL
    SELECT movies.title 
    FROM movies
    INNER JOIN movies_actors ON movies.id = movies_actors.movie_id
    INNER JOIN persons as actor ON movies_actors.actor_id = actors.id
    INNER JOIN persons as director ON movies.director_id = director.id    
    INNER JOIN movies_genres ON movies.id = movies_genres.movie_id
    INNER JOIN genres ON movies_genres.genre_id = genres.id
    WHERE actors.fullName = 'Samuel L Jackson'
    AND director.fullName LIKE '%Tarantino'
    AND genres.name in ('drama')    
    ORDER BY movies.title ASC
    ;
SQL;

$pdo = new PDO('sqlite:/tmp/db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare($query);
$stmt->execute();
$r = array_map(
    fn (array $record) => $record[0],
    $stmt->fetchAll()
);

echo "ee!\n";

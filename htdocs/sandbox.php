<?php

require '../vendor/autoload.php';

$pdo = \popcorn\lib\PDOHelper::getPDO();

$stmt = $pdo->query('SELECT * FROM pn_comments_kids WHERE entityId = 135659 ORDER BY createdAt ASC');
$stmt->execute();

$comments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

$group = [];

foreach ($comments as $comment) {

//	if ($comment['imagesCount'] > 0 && empty($comment['content'])) {
//		$group[$comment['owner']][] = $comment;
//	}


}

for ($i = 0; $i <= count($comments) - 1; $i++) {
	if (
		($comments[$i]['owner'] == $comments[$i + 1]['owner']) &&
		$comments[$i]['imagesCount'] > 0 && empty($comments[$i]['content'])
	) {
		$group[$comments[$i]['owner']][] = $comments[$i];
	} else {

	}
}

print '<pre>' . print_r($group, true) . '</pre>';
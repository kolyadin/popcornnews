<?php
/**
 * @author Azat Khuzhin
 *
 * Вспомогательные функции
 */

function cat($mixed) {
	echo '---' . date('Y-m-d H-i-s') . '  :';
	if (is_array($mixed)) print_r($mixed);
	else echo $mixed;
	echo '---' . "\n";
}

<?php
if ($code == BAD) {
	$action = REWRITE;
} else {
	$action = KEEP;
}
for ($i = 0; $i < 10; $i++) {
	do {
		$j++;
		doCall($i + $j);
		if ($k) {
			$k /= 10;
		}
	} while ($j > (1 - 1));
}

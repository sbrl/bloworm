<?php
/**
 * This code will benchmark your server to determine how high of a cost you can
 * afford. You want to set the highest cost that you can without slowing down
 * you server too much. 8-10 is a good baseline, and more is good if your servers
 * are fast enough. The code below aims for ≤ 50 milliseconds stretching time,
 * which is a good baseline for systems handling interactive logins.
 * 
 * Originallly from http://php.net/manual/en/function.password-hash.php
 * Adapted by @Starbeamrainbowlabs <sbrl@starbeamrainbowlabs.com> (https://starbeamrainbowlabs.com) (https://github.com/sbrl/)
 */

$timeTarget = 0.08; // 80 milliseconds 

$cost = 8;
do {
    $cost++;
    $start = microtime(true);
    password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
    $end = microtime(true);
} while (($end - $start) < $timeTarget);
header("content-type: text/plain");
echo("Appropriate Cost Found: $cost\n");
?>

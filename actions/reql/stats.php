<?php
/*
 *      _        _       
 *  ___| |_ __ _| |_ ___ 
 * / __| __/ _` | __/ __|
 * \__ \ || (_| | |_\__ \
 * |___/\__\__,_|\__|___/
 *                       
 */

$response = new api_response(200, 0, "stats");

$response->isadmin = user_isadmin($user);

$response->count = count($bookmarks);
$response->datasize = filesize(user_dirname($user) . "bookmarks.json");
$bookmarks = getjson(user_dirname($user) . "bookmarks.json");

$response->publickey = trim(file_get_contents(user_dirname($user) . "publickey"));

sendjson($response);
exit();

?>
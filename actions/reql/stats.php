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
$bookmarks = getjson(get_user_data_dir_name($user) . "bookmarks.json");
$response->count = count($bookmarks);
$response->datasize = filesize(get_user_data_dir_name($user) . "bookmarks.json");

sendjson($response);
exit();

?>
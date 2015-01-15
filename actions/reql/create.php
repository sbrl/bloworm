<?php
/* 
 *                      _       
 *   ___ _ __ ___  __ _| |_ ___ 
 *  / __| '__/ _ \/ _` | __/ _ \
 * | (__| | |  __/ (_| | ||  __/
 *  \___|_|  \___|\__,_|\__\___|
 *                              
 */

//url[, name, faviconurl, tags]
if(!isset($_GET["url"]))
	senderror(new api_error(400, 501, "You did not specify a url to add."));

$url = $_GET["url"];

if(isset($_GET["name"]))
	$name = $_GET["name"];
else
	$name = auto_find_name($url);

if(isset($_GET["faviconurl"]))
	$faviconurl = $_GET["faviconurl"];
else
	$faviconurl = auto_find_favicon_url($url);

if(isset($_GET["tags"]))
	$tags = explode(", ", ",", $_GET["tags"]);
else
	$tags = [];

$id = getid();

$bookmarks = getjson(get_user_data_dir_name($user) . "bookmarks.json");

// add the bookmark to the user's list
$bookmarks[] = [
	"id" => $id,
	"name" => utf8_encode($name),
	"url" => utf8_encode($url),
	"faviconurl" => utf8_encode($faviconurl),
	"tags" => $tags,
	"lastmodified" => time()
];

setjson(get_user_data_dir_name($user) . "bookmarks.json", $bookmarks);

// update the tags cache
$alltags = getjson(get_user_data_dir_name($user) . "tags.json");
foreach($tags as $tag)
{
	if(isset($alltags->$tag))
		$alltags->$tag++;
	else
		$alltags->$tag = 1;
}

$response = new api_response(201, 0, "create/success");
$response->id = $id;
$response->name = $name;

http_response_code($response->http_status);
header("x-new-bookmark-id: $response->id");
header("x-new-bookmark-name: $response->name");
sendjson($response);
exit();

?>

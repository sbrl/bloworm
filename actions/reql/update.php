<?php
/* 
 *                  _       _       
 *  _   _ _ __   __| | __ _| |_ ___ 
 * | | | | '_ \ / _` |/ _` | __/ _ \
 * | |_| | |_) | (_| | (_| | ||  __/
 *  \__,_| .__/ \__,_|\__,_|\__\___|
 *       |_|                        
 */
// id[, name, url, faviconurl, tags]
if(isset($_GET["id"]))
	$id_to_delete = $_GET["id"];
else
	senderror(new api_error(449, 502, "You didn't specify an `id` to update.\n\nThe appropriate GET parameter is `id`."));

if(!isset($_GET["name"]) and !isset($_GET["url"]) and !isset($_GET["faviconurl"]) and !isset($_GET["tags"]))
	senderror(new api_error(449, 503, "You didn't specify any parameters to update."));

$bookmarks = getjson(user_dirname() . "bookmarks.json"); // open the user's bookmarks for editing
for($i = count($bookmarks); $i >= 0; $i--)
{
	// loop over and find the bookmark with the appropriate id
	if($bookmarks[$i]->id)
	{
		// update the bookmark's details
		if(isset($_GET["name"]))
			$bookmarks[$i]->name = htmlentities($_GET["name"]);
		if(isset($_GET["url"]))
			$bookmarks[$i]->url = rawurlencode($_GET["url"]);
		if(isset($_GET["faviconurl"]))
			$bookmarks[$i]->faviconurl = rawurlencode($_GET["faviconurl"]);
		if(isset($_GET["tags"]))
		{
			$tags_to_add = explode(",", str_replace(", ", htmlentities($_GET["tags"])));
			$bookmarks[$i] = $tags_to_add;
		}
		$bookmarks[$i]->lastmodified = time(); // update the last modified counter
		break;
	}
}
setjson(user_dirname($user) . "bookmarks.json", $bookmarks); // save the bookmarks back to disk

sendjson(new api_response(200, 0, "bookmark-update/success"));

?>
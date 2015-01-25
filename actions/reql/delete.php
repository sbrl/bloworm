<?php
/*
 *      _      _      _       
 *   __| | ___| | ___| |_ ___ 
 *  / _` |/ _ \ |/ _ \ __/ _ \
 * | (_| |  __/ |  __/ ||  __/
 *  \__,_|\___|_|\___|\__\___|
 *                            
 */

if(isset($_GET["ids"]))
	$ids_to_delete = $_GET["ids"];
else
	senderror(new api_error(449, 504, "You didn't specify an `id` to delete.\n\nThe appropriate GET parameter is `id`."));

if(strpos($ids_to_delete, ",") !== false)
{
	$ids_to_delete = explode(",", str_replace(", ", ",", $ids_to_delete));
}
else
{
	$ids_to_delete = [$ids_to_delete];
}

$total_ids = count($ids_to_delete);

$bookmarks = getjson(user_dirname($user) . "bookmarks.json");
$all_tags = getjson(user_dirname($user) . "tags.json");
$deleted = 0;
for($i = count($bookmarks) - 1; $i >= 0; $i--)
{
	if(in_array($bookmarks[$i]->id, $ids_to_delete))
	{
		$tags = $bookmarks[$i]->tags; //save the tags
		unset($bookmarks[$i]);
		$deleted++;
		
		foreach($tags as $tag)
		{
			$all_tags->$tag -= 1;
			if($all_tags->$tag === 0)
				unset($all_tags->$tag);
		}
	}
}

$bookmarks = array_values($bookmarks);

setjson(user_dirname($user) . "tags.json", $all_tags);
if(!is_array($bookmarks)) die("Bookmarks array corrupt");
setjson(user_dirname($user) . "bookmarks.json", $bookmarks);

if($deleted >= count($ids_to_delete))
{
	$response = new api_response(200, 0, "Bookmark deletion completed.");
	sendjson($response);
	exit();
}
else
{
	senderror(new api_error(400, 511, "One or more bookmark ids were not found.\nTotal bookmark ids: $total_ids\nDeleted: " . $deleted . "\nFailed: " . $total_ids - ($deleted)));
}

?>

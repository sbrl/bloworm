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
	$ids_to_delete = $_GET["id"];
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

$bookmarks = getjson(user_dirname($user) . "bookmarks.json");
$all_tags = getjson(user_dirname($user) . "tags.json");
$deleted = 0;
for($i = count($bookmarks); $i >= 0; $i++)
{
	foreach($ids_to_delete as $id_to_delete)
	{
		if($bookmarks[$i]->id == $id_to_delete)
		{
			$tags = $bookmarks[$i]->tags; //save the tags
			unset($bookmarks);
			
			$deleted++;
			
			foreach($tags as $tag)
			{
				$tags->$tag--;
				if($tags->$tag == 0)
					unset($tags->$tag);
			}
		}
	}
}

$bookmarks = array_values($bookmarks);
$all_tags = array_values($all_tags);

setjson(user_dirname($user) . "tags.json");
setjson(user_dirname($user) . "bookmarks.json");

if($deleted >= count($ids_to_delete))
{
	$response = new api_response(200, 0, "Bookmark deletion completed.");
	sendjson($response);
	exit();
}
else
{
	senderror(new api_error(400, 509, "One or more bookmark ids were not found.\nTotal bookmark ids: " . count($ids_to_delete) . "\nDeleted: $deleted\nFailed: " . (count($ids_to_delete) - $deleted));
}

?>

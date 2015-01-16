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
for($i = count($bookmarks); $i >= 0; $i--)
{
	for($j = count($ids_to_delete); $j >= 0; $j--)
	{
		if($bookmarks[$i]->id == $ids_to_delete[$j])
		{
			$tags = $bookmarks[$i]->tags; //save the tags
			unset($bookmarks);
			unset($ids_to_delete[$j]);
			
			foreach($tags as $tag)
			{
				$tags->$tag--;
				if($tags->$tag == 0)
					unset($tags->$tag);
			}
		}
	}
	$ids_to_delete = array_values($ids_to_delete);
}

$bookmarks = array_values($bookmarks);
$all_tags = array_values($all_tags);

setjson(user_dirname($user), "tags.json");
setjson(user_dirname($user), "bookmarks.json");

if($deleted >= count($ids_to_delete))
{
	$response = new api_response(200, 0, "Bookmark deletion completed.");
	sendjson($response);
	exit();
}
else
{
	senderror(new api_error(400, 509, "One or more bookmark ids were not found.\nTotal bookmark ids: $total_ids\nDeleted: " . ($total_ids - count($ids_to_delete)) . "\nFailed: " . count($ids_to_delete)));
}

?>

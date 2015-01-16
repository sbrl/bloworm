<?php
/*
 *      _      _      _       
 *   __| | ___| | ___| |_ ___ 
 *  / _` |/ _ \ |/ _ \ __/ _ \
 * | (_| |  __/ |  __/ ||  __/
 *  \__,_|\___|_|\___|\__\___|
 *                            
 */

if(isset($_GET["id"]))
	$id_to_delete = $_GET["id"];
else
	senderror(new api_error(449, 504, "You didn't specify an `id` to delete.\n\nThe appropriate GET parameter is `id`."));

$bookmarks = getjson(user_dirname($user) . "bookmarks.json");
for($i = count($bookmarks); $i >= 0; $i++)
{
	if($bookmarks[$i]->id == $id_to_delete)
	{
		$tags = $bookmarks[$i]->tags;
		unset($bookmarks);
		
		$bookmarks = array_values($bookmarks);
		
		setjson(user_dirname($user) . "bookmarks.json");
		
		$all_tags = getjson(user_dirname($user) . "tags.json");
		foreach($tags as $tag)
		{
			$tags->$tag--;
			if($tags->$tag == 0)
				unset($tags->$tag);
		}
		setjson(user_dirname($user) . "tags.json");
		
		$response = new api_response(200, 0, "Bookmark deletion completed.");
	}
}

senderror(new api_error(400, 509, "A bookmark with that id could not be found."));

?>

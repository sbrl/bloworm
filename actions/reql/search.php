<?php
/*
 *                          _     
 *  ___  ___  __ _ _ __ ___| |__  
 * / __|/ _ \/ _` | '__/ __| '_ \ 
 * \__ \  __/ (_| | | | (__| | | |
 * |___/\___|\__,_|_|  \___|_| |_|
 *                                
 */

if(!isset($_GET["query"]))
{
	//return a list of all the bookmarks the user has
	$response = new api_response(200, 0, "search/all-bookmarks");
	$response->bookmarks = getjson(get_user_data_dir_name($user) . "bookmarks.json");
	sendjson($response);
	exit();
}

$query = trim($_GET["query"]);

if(isset($_GET["limit"]))
	$limit = $_GET["limit"];
else
	$limit = -1;

// extract all the tags
$terms = explode(" ", $query);
$tags = [];
$no_keywords = true; // whether the query string contains any keywords
foreach($terms as $term)
{
	if(substr(trim($term), 0, 1) === "#")
		$tags[] = substr(trim($term), 1);
	else
		$no_keywords = false;
}


$all_bookmarks = getjson(get_user_data_dir_name($user) . "bookmarks.json");

// filter by tag(s)
if(count($tags) > 0)
{
	$all_bookmarks = array_filter($all_bookmarks, function($bookmark) {
		$has_tag = false;
		foreach($tags as $tag)
		{
			foreach($bookmark->tags as $bookmark_tag)
			{
				if($bookmark_tag == $tag)
				{
					$has_tag = true;
					break 2;
				}
			}
		}
		return $has_tag;
	});
}

// don't fuzzy search if we only have tags and no keywords
if(!$no_keywords)
	$matching_bookmarks = fuzzy_search($query, $all_bookmarks);

// limit the number of bookmarks we respond with in asked to do so
if($limit > 0)
	$matching_bookmarks = array_slice($matching_bookmarks, 0, $limit);

$response = new api_response(200, 0, "search/query-levenshtein");
$response->bookmarks = $matching_bookmarks;

http_response_code($response->http_status);
sendjson($response);
exit();
?>
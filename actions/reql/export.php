<?php
/*
 *                             _   
 *   _____  ___ __   ___  _ __| |_ 
 *  / _ \ \/ / '_ \ / _ \| '__| __|
 * |  __/>  <| |_) | (_) | |  | |_ 
 *  \___/_/\_\ .__/ \___/|_|   \__|
 *           |_|                   
 */

// the filenames to add to the archive
$filenames = [
	"bookmarks.json",
	"tags.json"
];

$archive = new stdClass();
foreach($filenames as $filename)
{
	$archive->$filename = getjson(user_dirname($user) . $filename);
}

$archive_string = json_encode($archive, JSON_PRETTY_PRINT);


header("content-type: application/gzip-compressed");
header("content-disposition: attachment; filename=$user-export.json.gz");
echo(gzencode($archive_string));

?>
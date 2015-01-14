<?php
/* 
 *                  _       _       
 *  _   _ _ __   __| | __ _| |_ ___ 
 * | | | | '_ \ / _` |/ _` | __/ _ \
 * | |_| | |_) | (_| | (_| | ||  __/
 *  \__,_| .__/ \__,_|\__,_|\__\___|
 *       |_|                        
 */
//id[, name, url, faviconurl, tags]
if(isset($_GET["id"]))
	$id_to_delete = $_GET["id"];
else
	senderror(new api_error(449, 502, "You didn't specify an `id` to update.\n\nThe appropriate GET parameter is `id`."));

// error code 503 is reserved for requests when no extra pieces of data were specified to update

http_response_code(501);
exit("This action is not implemented yet.");

?>
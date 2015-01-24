<?php
/*
 * @summary Fetches and decodes a json file.
 * 
 * @param $filename - The path to the json file.
 * 
 * @returns The decoded json.
 */
function getjson($filename) { return json_decode(file_get_contents($filename)); }

/*
 * @summary Save something to a file as json.
 * 
 * @param $filename - The path to the file that should be written to.
 * @param $thing - The thing to save.
 */
function setjson($filename, $thing, $create_backup = true)
{
	if(file_exists($filename))
	{
		if(!rename($filename, "$filename.backup"))
			senderror(new api_error(507, 702, "Failed to create backup of $filename"));
	}
	if(!file_put_contents($filename, json_encode($thing, JSON_PRETTY_PRINT)))
		senderror(new api_error(507, 701, "Failed to save json to file $filename"));
}

?>

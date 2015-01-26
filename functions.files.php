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

/*
 * @summary Easily create a file / folder tree
 * 
 * @param $tree - An array describing the tree to create.
 */
function create_tree($tree)
{
	foreach($tree as &$instruction)
	{
		if(!isset($instruction["mode"]))
			$instruction["mode"] = 0775;
		
		switch($instruction["type"])
		{
			case "file":
				if(!isset($instruction["content"]))
					$instruction["content"] = "";
				
				try {
					file_put_contents($instruction["path"], $instruction["content"]);
				} catch (Exception $error) {
					senderror(new api_error(507, 704, "Failed to create file " . $instruction["path"], $error));
				}
				try {
					chmod($instruction["path"], $instruction["mode"]);
				} catch (Exception $error) {
					senderror(new api_error(507, 705, "Failed to set permissions on " . $instruction["path"], $error));
				}
				break;
			
			case "folder":
				if(!isset($instruction["mode"]))
					$instruction["mode"] = "0775";
				try {
					mkdir($instruction["path"], $instruction["mode"], true);
				} catch (Exception $error) {
					senderror(new api_error(507, 706, "Failed to create directory " . $instruction["path"]));
				}
				break;
			
			default:
				senderror(new api_error(500, 707, "Unknown file tree entry type: " . $instruction["type"]));
		}
	}
}

?>

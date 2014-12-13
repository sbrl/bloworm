<?php
/*
 * Blow Worm API
 ***********************
 * HTTP status codes:
	* 422 - response understood *and* well formed, but validation failed
 * Actions:
	* login
		* username
		* password
	* logout
	* create - create a new bookmark
		* name
		* url
		* [optional] faviconurl
		* [optional] tags
	* delete - delete a bookmark
		* id
	* update - update a bookmark's details
		* id
		* [optional] name
		* [optional] url
		* [optional] tags
		* [optional] faviconurl
	* search - search for bookmarks
		* query
	* share - share bookmarks with others //todo think about this and design the api properly
		* [optional] ids - a list of bookmark ids to share, separated by commas
		* [optional] tag - the tag to share
	* view - View a shared tag
		* tag - the tag to view
	* usermod - change user settings, like one's password
		* key - Possible values: password
		* value
 */

require("settings.php");
require("functions.core.php");

if(!isset($_GET["action"]))
	senderror(422, "No action was specified.");

if(!file_exists("data/"))
{
	if(!mkdir("data/", 0700))
		senderror(507, 3, "Failed to create the `data/` folder.");
	if(!file_put_contents("data/next.id", 0))
		senderror(507, 4, "Failed to create `data/next.id`.");
	if(!mkdir("data/users/admin", 0700, true))
		senderror(507, 5, "Failed to initialise first user");
	if(!file_put_contents("data/users/admin/password", hash_password("blow-worm")))
		senderror(507, 6, "Failed to set initial user's password.");
	if(!file_put_contents("data/user/admin/bookmarks.json", "[]"))
		senderror(507, 7, "Failed to create initial user's bookmark storage file.");
}

//check the user's login here and set a variable telling the rest of the code whether the user is logged in
//todo split this into 2 switches: one for those who are logged in, and one of those who are not.
switch($_GET["action"])
{
	case "login":
		break;
	
	case "logout":
		break;
	
	case "create":
		break;
	
	case "delete":
		break;
	
	case "update":
		break;
	
	case "search":
		break;
	
	case "view":
		break;
	
	case "share":
		break;
	
	case "usermod":
		if(!isset($_GET["key"]))
			senderror(422, 1, "No key was specified.");
		if(!isser($_GET["value"]))
			senderror(422, 2, "No value was specified.");
		
		
		break;
	
	default:
		senderror(404, "That `action` was not recognised.");
}
?>
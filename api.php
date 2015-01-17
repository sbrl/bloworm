<?php
/*
 * Blow Worm API
 ***********************
 * HTTP status codes:
	* 422 - response understood *and* well formed, but validation failed
	* 449 - missing required parameter
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
	* stats - view your stats
	* search - search for bookmarks
		* query
		* [optional] limit
	* share - share bookmarks with others
		* tag - the tag to share
	* unshare - unshare a tag
		* tag - the tag to unshare
	* view - View a shared tag
		* tag - the tag to view
	* usermod - change user settings, like one's password
		* key - Possible values: password
		* value
 */

////////////////////////////////////////////////////////
///////////////////// Requirements /////////////////////
////////////////////////////////////////////////////////
require("functions.errors.php");
require("settings.php");
require("functions.core.php");
require("functions.users.php");
require("functions.network.php");

/////////////////////////////////////////////////////////
///////////////////////// Paths /////////////////////////
/////////////////////////////////////////////////////////
$paths = [
	"sessionkeys" => "data/login_sessions.json"
];

////////////////////////////////////////////////////////
/////////////////// Input Validation ///////////////////
////////////////////////////////////////////////////////
if(!isset($_GET["action"]))
{
	http_response_code(300);
	header("content-type: text/plain");
	echo("Welcome to the blow worm api at " . gethostname() . ".
	
At some point some API help will be printed here instead.\n\n");
	exit();
}

if(preg_match("/[^a-z]/i", trim($_GET["action"])))
{
	senderror(new api_error(400, 402, "Potentially dangerous characters were detected in the action you specified."));
}

///////////////////////////////////////////////////////
//////////////////// Initial Setup ////////////////////
///////////////////////////////////////////////////////
if(!file_exists("data/"))
{
	$initial_structure = [
		[ "type" => "folder", "path" => "data/" ],
		[ "type" => "file", "path" => "data/next.id", "content" => 0 ],
		[ "type" => "folder", "path" => "data/users" ],
		[ "type" => "folder", "path" => "data/users/admin" ],
		[ "type" => "file", "path" => "data/users/admin/password", "content" => hash_password("bloworm") ],
		[ "type" => "file", "path" => "data/users/admin/isadmin", "content" => "true" ],
		[ "type" => "file", "path" => "data/users/admin/bookmarks.json", "content" => "[]" ],
		[ "type" => "file", "path" => "data/users/admin/tags.json", "content" => "{}" ],
		[ "type" => "file", "path" => $paths["sessionkeys"], "content" => "[]" ],
		[ "type" => "file", "path" => "data/userlist.json", "content" => "[\"admin\"]"]
	];
	
	foreach($initial_structure as $to_create)
	{
		switch($to_create["type"])
		{
			case "folder":
				if(!mkdir($to_create["path"], 0700, true))
					senderror(new api_error(507, 301, "Failed to create directory: " . $to_create["path"]));
				break;
			
			case "file":
				if(!file_put_contents($to_create["path"], $to_create["content"]))
					senderror(new api_error(507, 302, "Failed to create file: " . $to_create["path"]));
				break;
			
			default:
				senderror(new api_error(500, 303, "Unknown setup entry type: " . $to_create["type"]));
		}
	}
}
///////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// Login Checker //////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////
// function to clear old session keys
function clear_old_sessions()
{
	global $paths;
	
	$sessions = getjson($paths["sessionkeys"]); // read in the current sessions
	array_filter($sessions, function($session) { // loop over all the sessions and remove expired ones
		if($session->expires < time())
			unset($session);
	});
	array_values($sessions); // reset the array keys ready for saving
	setjson($paths["sessionkeys"], $sessions); // save the sessions back to disk
}

$logged_in = false;
if(isset($_COOKIE[$cookie_names["session"]]) and isset($_COOKIE[$cookie_names["user"]]))
{
	//the user might be loggged in
	$sessions = getjson($paths["sessionkeys"]);
	foreach($sessions as $session)
	{
		if($session->key == $_COOKIE[$cookie_names["session"]] and
		   $session->user == $_COOKIE[$cookie_names["user"]])
		{
			if($session->expires <= time())
			{
				//the session key has expired, delete it from the list
				senderror(new api_error(419, 123, "Your session has expired. Please try logging in again."));
			}
			/* by this point we have verified:
				* The session key is ok
				* The session key has not expired
				* The session key belongs to the requesting user */
			
			$user = $_COOKIE[$cookie_names["user"]];
			$logged_in = true;
			break; //no need to loop over the rest of the session keys
		}
	}
	clear_old_sessions();
}
///////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////

if(file_exists("actions/nreql/" . $_GET["action"] . ".php"))
{
	require_once("actions/nreql/" . $_GET["action"] . ".php");
	exit();
}

if(file_exists("actions/reql/" . $_GET["action"] . ".php"))
{
	// make sure that the user is logged in
	if(!$logged_in)
	{
		senderror(new api_error(401, 124, "You need to log in to perform that action."));
	}
	
	require_once("actions/reql/" . $_GET["action"] . ".php");
	exit();
}

if(file_exists("actions/reqadm/" . $_GET["action"] . ".php"))
{
	// make sure that the user is logged in
	if(!$logged_in)
	{
		senderror(new api_error(401, 125, "You need to be logged in to perform that administrator action."));
	}
	
	// make sure that the user is an administrator
	if(trim(file_get_contents("data/users/$user/isadmin")) !== "true")
	{
		senderror(new api_error(401, 126, "You must be an administrator to perform that action. Please contact an administrator of this Blow Worm installation."));
	}
	
	require_once("actions/reqadm/" . $_GET["action"] . ".php");
	exit();
}

senderror(new api_error(404, 401, "That `action` was not recognised."));

?>

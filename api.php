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

////////////////////////////////////////////////////////
///////////////////// Requirements /////////////////////
////////////////////////////////////////////////////////
require("settings.php");
require("functions.core.php");
require("funtions.network.php");

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
	senderror(422, "No action was specified.");

///////////////////////////////////////////////////////
//////////////////// Initial Setup ////////////////////
///////////////////////////////////////////////////////
if(!file_exists("data/"))
{
	$initial_structure = [
		[ "type" => "folder", "path" => "data/" ],
		[ "type" => "file", "path" => "data/next.id", "content" => 0 ],
		[ "type" => "folder", "path" => "data/admin" ],
		[ "type" => "file", "path" => "data/admin/password", "content" => hash_password("blow-worm") ],
		[ "type" => "file", "path" => "data/user/admin/bookmarks.json", "content" => "[]" ],
		[ "type" => "file", "path" => $paths["sessionkeys"], "content" => "[]" ],
		[ "type" => "file", "path" => "data/userlist.json", "content" => "[\"admin\"]"]
	];
	
	foreach($initial_structure as $to_create)
	{
		switch($to_create)
		{
			case "folder":
				if(!mkdir($to_create["path"], 0700, true))
					senderror(new api_error(507, 3, "Failed to create directory: " . $to_create["path"]));
				break;
			
			case "file":
				if(!file_put_contents($to_create["path"], $to_create["content"]))
					senderror(new api_error(507, 3, "Failed to create file: " . $to_create["path"]));
				break;
			
			default:
				senderror(new api_error(500, 4, "Unknown setup entry: " . $to_create["type"]));
		}
	}
}
///////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// Login Checker //////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////
$logged_in = false;
if(isset($_COOKIE[$cookie_names["session"]]) and isset($_COOKIE[$cookie_names["user"]]))
{
	//the user might be loggged in
	$sessions = getjson($paths["sessionkeys"]);
	$i = 0;
	foreach($sessions as $session)
	{
		if($session->key == $_COOKIE[$cookie_names["session"]] and
		   $session->user == $_COOKIE[$cookie_names["user"]])
		{
			if($session->expires <= time())
			{
				//the session key has expired, delete it from the list
				unset($sessions[$i]);
				$sessions = array_values($sessions); //reset the array keys
				setjson($paths["sessionkeys"], $sessions); //save the session keys to disk
				senderror(new api_error(419, 12, "Your session has expired. Please try logging in again."));
			}
			/* by this point we have verified:
				* The session key is ok
				* The session key has not expired
				* The session key belongs to the requesting user */
			
			$user = $_COOKIE[$cookie_names["user"]];
			$logged_in = true;
			break; //no need to loop over the rest of the session keys
		}
		$i++;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////

//todo split this into 2 switches: one for those who are logged in, and one of those who are not.
switch($_GET["action"])
{
	case "login":
		if(!isset($_GET["user"]) or !isset($_GET["pass"]))
			senderror(new api_error(422, 5, "No username or password was present in the request."));
		
		if(!user_exists($_GET["user"]))
			senderror(new api_ error(401, 6, "The username and/or password given was/were incorrect."));
		
		try {
			$user_pass_hash = file_get_contents("data/users/" . $_GET["user"] . "/password");
		} catch(Exception $e)
		{
			senderror(500, 7, "Failed to read in password hash.");
		}
		
		if(!password_verify($_GET["pass"], $user_pass_hash))
			senderror(new api_ error(401, 6, "The username and/or password given was/were incorrect."));
		
		//by this point we have verified that the user's credientials are correct
		
		//todo rehash the password if necessary (use password_needs_rehash())
		
		$login_sessions = getjson($paths["sessionkeys"]);
		$sessionkey = hash("sha256", openssl_random_pseudo_bytes($session_key_length));
		$login_sessions[] = [
			"user" => $_GET["user"],
			"key" => $sessionkey,
			"expires" => time() * $session_key_valid_time
		];
		
		setcookie("blow-worm-user", $_GET["user"], time() * $session_key_valid_time);
		setcookie("blow-worm-session", $sessionkey, time() * $session_key_valid_time);
		http_response_code(200);
		exit("Login successful."); //todo convert this to json
		var_dump($sessionkey);
		
		exit();
		break;
	
	case "search":
		http_response_code(501);
		exit();
		break;
	
	case "view":
		http_response_code(501);
		exit();
		break;
	
	case "share":
		http_response_code(501);
		exit();
		break;

}

if(!$isloggedin)
{
	senderror(new api_error(401, 13, "You need to log in to perform that action."));
}

switch($_GET["action"])
{
	case "logout":
		if(!isset($_COOKIE[$cookie_names["session"]]))
			senderror(new api_error(412, 8, "Failed to find session key cookie (you must already be logged out)."));
		
		if(!isset($_COOKIE[$cookie_names["user"]]))
			senderror(new api_error(412, 9, "Failed to find username in cookie (you *may* already be logged out)."));
		
		$sessions = getjson($paths["sessionkeys"]);
		for($i = 0; $i < count($sessions); $i++)
		{
			if($sessions[$i]["key"] == $_COOKIE[$cookie_names["session"]] and
			  $sessions[$i]["user"] == $_COOKIE[$cookie_names["user"]])
			{
				unset($sessions[$i]); //remove the session key
				$sessions = array_values($sessions); //reset all the values
				setjson($paths["sessionkeys"], $sessions); //save the sessions back to disk
				exit("Log out completed."); //todo convert this to json
			}
		}
		senderror(new api_error(422, 11, "Failed to log out - Either your session key or username were invalid."));
		break;
	
	case "create":
		//url[, name, faviconurl, tags]
		if(!isset($_GET["url"]))
			senderror(new api_error(400, 15, "You did not specify a url to add."));
		
		$url = $_GET["url"];
		
		if(isset($_GET["name"]))
			$name = $_GET["name"];
		else
			$name = auto_find_name($url);
		
		if(isset($_GET["faviconurl"]))
			$faviconurl = $_GET["faviconurl"];
		else
			$faviconurl = auto_find_favicon_url($url);
		
		if(isset($_GET["tags"]))
			$tags = explode(", ", ",", $_GET["tags"]);
		else
			$tags = [];
		
		$id = getid();
		
		$bookmarks = getjson("./data/$user/bookmarks.json");
		
		//add the bookmark to the user's list
		$bookmarks[] = [
			"id" => $id,
			"name" => utf8_encode($name),
			"url" => utf8_encode($url),
			"faviconurl" => utf8_encode($faviconurl),
			"tags" => $tags
		];
		
		setjson("./data/$user/bookmarks.json", $bookmarks);
		
		http_response_code(201);
		header("x-new-bookmark-id: $id");
		header("x-new-bookmark-name: $name");
		exit("New bookmark added successfully.\nid: $id");

		break;
	
	case "delete":
		http_response_code(501);
		exit();
		break;
	
	case "update":
		http_response_code(501);
		exit();
		break;
	
	case "usermod":
		if(!isset($_GET["key"]))
			senderror(new api_error(422, 1, "No key was specified."));
		if(!isser($_GET["value"]))
			senderror(new api_error(422, 2, "No value was specified."));
		
		http_response_code(501);
		exit();
		
		break;
	
	default:
		senderror(new api_error(404, 14, "That `action` was not recognised."));
}
?>

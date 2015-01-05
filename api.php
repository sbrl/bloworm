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
		[ "type" => "file", "path" => "data/users/admin/password", "content" => hash_password("blow-worm") ],
		[ "type" => "file", "path" => "data/users/admin/isadmin", "content" => "true" ],
		[ "type" => "file", "path" => "data/users/admin/bookmarks.json", "content" => "[]" ],
		[ "type" => "file", "path" => $paths["sessionkeys"], "content" => "[]" ],
		[ "type" => "file", "path" => "data/userlist.json", "content" => "[\"admin\"]"]
	];
	
	foreach($initial_structure as $to_create)
	{
		switch($to_create["type"])
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
				senderror(new api_error(500, 4, "Unknown setup entry type: " . $to_create["type"]));
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
if(!isset($_GET["action"]))
	exit();

switch($_GET["action"])
{
	/*
	 *  _             _       
	 * | | ___   __ _(_)_ __  
	 * | |/ _ \ / _` | | '_ \ 
	 * | | (_) | (_| | | | | |
	 * |_|\___/ \__, |_|_| |_|
	 *          |___/         
	 */
	case "login":
		if(!isset($_POST["user"]) or !isset($_POST["pass"]))
			senderror(new api_error(449, 5, "No username and/or password was present in the body of the request.\n\nThe appropriate POST parameters are `user` and `pass`."));
		
		if(!user_exists($_POST["user"]))
			senderror(new api_error(401, 6, "The username and/or password given was/were incorrect."));
		
		try {
			$user_pass_hash = file_get_contents(get_user_data_dir_name($_POST["user"]) . "password");
		} catch(Exception $e)
		{
			senderror(500, 7, "Failed to read in password hash.");
		}
		
		if(!password_verify($_POST["pass"], $user_pass_hash))
			senderror(new api_error(401, 6, "The username and/or password given was/were incorrect."));
		
		//by this point we have verified that the user's credientials are correct
		
		//todo rehash the password if necessary (use password_needs_rehash())
		
		$login_sessions = getjson($paths["sessionkeys"]);
		$sessionkey = hash("sha256", openssl_random_pseudo_bytes($session_key_length));
		$login_sessions[] = [
			"user" => utf8_encode($_POST["user"]),
			"key" => utf8_encode($sessionkey),
			"expires" => time() + $session_key_valid_time
		];
		setjson($paths["sessionkeys"], json_encode($login_sessions, JSON_PRETTY_PRINT));
		
		setcookie("blow-worm-user", $_POST["user"], time() + $session_key_valid_time);
		setcookie("blow-worm-session", $sessionkey, time() + $session_key_valid_time);
		
		http_response_code(200);
		$response = new api_response(200, 0, "Login successful.");
		$response->user = $_POST["user"];
		$response->sessionkey = $sessionkey;
		$response->expire_time = time() + $session_key_valid_time;
		sendjson($response);
		exit();
		break;
	
	/*
	 *       _               _    _             _       
	 *   ___| |__   ___  ___| | _| | ___   __ _(_)_ __  
	 *  / __| '_ \ / _ \/ __| |/ / |/ _ \ / _` | | '_ \ 
	 * | (__| | | |  __/ (__|   <| | (_) | (_| | | | | |
	 *  \___|_| |_|\___|\___|_|\_\_|\___/ \__, |_|_| |_|
	 *                                    |___/         
	 */
	case "checklogin":
		$response = new api_response(200, 0, "checklogin");
		$response->logged_in = $logged_in;
		if($logged_in)
		{
			$response->user = $user;
			$response->sessionkey = $_COOKIE[$cookie_names["session"]];
		}
		else
		{
			$response->user = false;
			$response->sessionkey = false;
		}
		sendjson($response);
		exit();
		break;
	
	/*
	 *        _               
	 * __   _(_) _____      __
	 * \ \ / / |/ _ \ \ /\ / /
	 *  \ V /| |  __/\ V  V / 
	 *   \_/ |_|\___| \_/\_/  
	 *                        
	 */
	case "view":
		http_response_code(501);
		exit("This action is not implemented yet.");
		break;
}

if(!$logged_in)
{
	senderror(new api_error(401, 13, "You need to log in to perform that action."));
}

switch($_GET["action"])
{
	/*
	 *  _                         _   
	 * | | ___   __ _  ___  _   _| |_ 
	 * | |/ _ \ / _` |/ _ \| | | | __|
	 * | | (_) | (_| | (_) | |_| | |_ 
	 * |_|\___/ \__, |\___/ \__,_|\__|
	 *          |___/                 
	 */
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
				
				$response = new api_response(200, 0, "login/success");
				sendjson($response);
				exit();
			}
		}
		
		senderror(new api_error(422, 11, "Failed to log out - Either your session key or username were invalid."));
		break;
	
	/* 
	 *                      _       
	 *   ___ _ __ ___  __ _| |_ ___ 
	 *  / __| '__/ _ \/ _` | __/ _ \
	 * | (__| | |  __/ (_| | ||  __/
	 *  \___|_|  \___|\__,_|\__\___|
	 *                              
	 */
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
		
		$bookmarks = getjson(get_user_data_dir_name($user) . "bookmarks.json");
		
		//add the bookmark to the user's list
		$bookmarks[] = [
			"id" => $id,
			"name" => utf8_encode($name),
			"url" => utf8_encode($url),
			"faviconurl" => utf8_encode($faviconurl),
			"tags" => $tags
		];
		
		setjson(get_user_data_dir_name($user) . "bookmarks.json", $bookmarks);
		
		$response = new api_response(201, 0, "create/success");
		$response->id = $id;
		$response->name = $name;
		
		http_response_code($response->http_status);
		header("x-new-bookmark-id: $response->id");
		header("x-new-bookmark-name: $response->name");
		sendjson($response);
		exit();
		break;
	
	/*
	 *      _      _      _       
	 *   __| | ___| | ___| |_ ___ 
	 *  / _` |/ _ \ |/ _ \ __/ _ \
	 * | (_| |  __/ |  __/ ||  __/
	 *  \__,_|\___|_|\___|\__\___|
	 *                            
	 */
	case "delete":
		if(isset($_GET["id"]))
			$id_to_delete = $_GET["id"];
		else
			senderror(new api_error(449, 18, "You didn't specify an `id` to delete.\n\nThe appropriate GET parameter is `id`."));
		
		http_response_code(501);
		exit("This action is not implemented yet.");
		break;
	
	/* 
	 *                  _       _       
	 *  _   _ _ __   __| | __ _| |_ ___ 
	 * | | | | '_ \ / _` |/ _` | __/ _ \
	 * | |_| | |_) | (_| | (_| | ||  __/
	 *  \__,_| .__/ \__,_|\__,_|\__\___|
	 *       |_|                        
	 */
	case "update":
		//id[, name, url, faviconurl, tags]
		if(isset($_GET["id"]))
			$id_to_delete = $_GET["id"];
		else
			senderror(new api_error(449, 19, "You didn't specify an `id` to update.\n\nThe appropriate GET parameter is `id`."));
		
		
		http_response_code(501);
		exit("This action is not implemented yet.");
		break;
	
	/*
	 *                          _     
	 *  ___  ___  __ _ _ __ ___| |__  
	 * / __|/ _ \/ _` | '__/ __| '_ \ 
	 * \__ \  __/ (_| | | | (__| | | |
	 * |___/\___|\__,_|_|  \___|_| |_|
	 *                                
	 */
	case "search":
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
		break;
	
	/*
	 *      _        _       
	 *  ___| |_ __ _| |_ ___ 
	 * / __| __/ _` | __/ __|
	 * \__ \ || (_| | |_\__ \
	 * |___/\__\__,_|\__|___/
	 *                       
	 */
	case "stats":
		$response = new api_response(200, 0, "stats");
		$bookmarks = getjson(get_user_data_dir_name($user) . "bookmarks.json");
		$response->count = count($bookmarks);
		$response->datasize = filesize(get_user_data_dir_name($user) . "bookmarks.json");
		
		sendjson($response);
		exit();
		break;
	
	/*
	 *      _                    
	 *  ___| |__   __ _ _ __ ___ 
	 * / __| '_ \ / _` | '__/ _ \
	 * \__ \ | | | (_| | | |  __/
	 * |___/_| |_|\__,_|_|  \___|
	 *                           
	 */
	case "share":
		if(isset($_GET["tags"]))
			$tags = explode(",", str_replace(", ", ",", $_GET["tags"]));
		else
			senderror(new api_error(449, 16, "You didn't specify any `tags` to share.\n\nThe appropriate GET parameter is `tags`."));
		
		
		
		http_response_code(501);
		exit("This action is not implemented yet.");
		break;
	
	/*
	 *                  _                    
	 *  _   _ _ __  ___| |__   __ _ _ __ ___ 
	 * | | | | '_ \/ __| '_ \ / _` | '__/ _ \
	 * | |_| | | | \__ \ | | | (_| | | |  __/
	 *  \__,_|_| |_|___/_| |_|\__,_|_|  \___|
	 *                                       
	 */
	case "unshare":
		if(isset($_GET["tags"]))
			$tags = explode(",", str_replace(", ", ",", $_GET["tags"]));
		else
			senderror(new api_error(449, 17, "You didn't specify any `tags` to share.\n\nThe appropriate GET parameter is `tags`."));
		
		
		
		http_response_code(501);
		exit("This action is not implemented yet.");
		break;
	
	/*
	 *                                          _ 
	 *  _   _ ___  ___ _ __ _ __ ___   ___   __| |
	 * | | | / __|/ _ \ '__| '_ ` _ \ / _ \ / _` |
	 * | |_| \__ \  __/ |  | | | | | | (_) | (_| |
	 *  \__,_|___/\___|_|  |_| |_| |_|\___/ \__,_|
	 *                                            
	 */
	case "usermod":
		//key, value
		if(isset($_GET["key"]))
			$key = $_GET["key"];
		else
			senderror(new api_error(449, 1, "No key was specified.\n\nThe appropriate GET parameter is `key`."));
		if(isset($_GET["value"]))
			$value = $_GET["value"];
		else
			senderror(new api_error(449, 2, "No value was specified.\n\nThe approapriate GET parameter is `value`."));
		
		http_response_code(501);
		exit("This action is not implemented yet.");
		
		break;
	
	default:
		senderror(new api_error(404, 14, "That `action` was not recognised."));
}
?>

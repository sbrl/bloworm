<?php
/*
 *            _     _                     
 *   __ _  __| | __| |_   _ ___  ___ _ __ 
 *  / _` |/ _` |/ _` | | | / __|/ _ \ '__|
 * | (_| | (_| | (_| | |_| \__ \  __/ |   
 *  \__,_|\__,_|\__,_|\__,_|___/\___|_|   
 *                               %adduser%
 */

if(!isset($_GET["newusername"]))
	senderror(new api_response(449, 581, "No username was specified for the new account."));

if(preg_match("/[^a-z0-9\-_]/i", $_GET["newusername"]))
	senderror(new api_response(400, 582, "User account creation failed: Potentially dangerous characters were found in the new username."));
else
	$newusername = $_GET["newusername"];

if(file_exists(user_dirname($_GET["newusername"])))
	senderror(507, 151, "That user account already exists.");

$password = base_convert(uniqid("", true), 10, 36);

// create a the new user's directory tree
create_tree([
	[ "type" => "folder", "mode" => 0700, "path" => "users/$newusername" ],
	[ "type" => "file", "mode" => 0700 , "path" => "users/$newusername/password", "content" => hash_password($password)],
	[ "type" => "file", "mode" => 0700, "path" => "users/$newusername/bookmarks.json", "content" => "[]" ],
	[ "type" => "file", "mode" => 0700, "path" => "users/$newusername/isadmin", "content" => "false" ],
	[ "type" => "file", "mode" => 0700, "path" => "users/$newusername/tags.json", "content" => "{}" ]
]);

// add the new username to the user list
$userlist = getjson("data/userlist.json");
$userlist[] = $newusername;
setjson("data/userlist.json", $userlist);

$response = new api_response(201, 0, "New user created successfully.");
$response->username = $newusername;
$response->password = $password;
sendjson($response);
exit();

?>

<?php
/*
 *  ____        _     _ _           _    ____ ___ 
 * |  _ \ _   _| |__ | (_) ___     / \  |  _ \_ _|
 * | |_) | | | | '_ \| | |/ __|   / _ \ | |_) | | 
 * |  __/| |_| | |_) | | | (__   / ___ \|  __/| | 
 * |_|    \__,_|_.__/|_|_|\___| /_/   \_\_|  |___|
 *                                                
 */
// ----------------------------------------------

require_once("settings.default.php");
if(file_exists("settings.php"))
	require_once("settings.php");

require_once("functions.core.php");
require_once("functions.errors.php");
require_once("functions.files.php");
require_once("functions.network.php");
require_once("functions.users.php");

// ----------------------------------------------

header("access-control-allow-origin: *");

// ----------------------------------------------

if(!isset($_GET["user"]))
{
	http_response_code(449);
	exit("No 'user' specified");
}
$user = $_GET["user"];
if(preg_match("/[^a-z0-9-_ ]/i", $user))
{
	http_response_code(400);
	exit("Invalid 'user'.");
}

if(!isset($_GET["key"]))
{
	http_response_code(449);
	exit("No 'key' specified");
}
$key = $_GET["key"];

if(!file_exists(user_dirname($user)) or user_publickey($user) !== $key)
{
	http_response_code(401);
	exit("Invalid 'user' or 'key'");
}

// the user and key are valid

require_once("actions/reql/create.php");

?>

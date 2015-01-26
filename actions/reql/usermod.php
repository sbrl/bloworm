<?php
/*
 *                                          _ 
 *  _   _ ___  ___ _ __ _ __ ___   ___   __| |
 * | | | / __|/ _ \ '__| '_ ` _ \ / _ \ / _` |
 * | |_| \__ \  __/ |  | | | | | | (_) | (_| |
 *  \__,_|___/\___|_|  |_| |_| |_|\___/ \__,_|
 *                                            
 */

//key, value
if(isset($_POST["key"]))
	$key = $_POST["key"];
else
	senderror(new api_error(449, 507, "No key was specified.\n\nThe appropriate GET parameter is `key`."));
if(isset($_POST["value"]))
	$value = $_POST["value"];
else
	senderror(new api_error(449, 508, "No value was specified.\n\nThe approapriate GET parameter is `value`."));

switch($key)
{
	case "password":
		if(!isset($_POST["oldpass"]))
			senderror(new api_error(449, 509, "You did't specify your old password, so blow worm can't change it to your new one."));
		
		if(!password_verify($_POST["oldpass"], file_get_contents(user_dirname($user) . "password")))
			senderror(new api_error(401, 130, "You didn't type your old password correctly."));
		
		file_put_contents(user_dirname($user) . "password", hash_password($_POST["value"]));
		
		$response = new api_response(200, 0, "usermod/passwordchange/success");
		sendjson($response);
		break;
	
	default:
		senderror(new api_error(400, 510, "That user setting key was not found."));
}

?>

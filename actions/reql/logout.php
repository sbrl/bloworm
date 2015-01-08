<?php
/*
 *  _                         _   
 * | | ___   __ _  ___  _   _| |_ 
 * | |/ _ \ / _` |/ _ \| | | | __|
 * | | (_) | (_| | (_) | |_| | |_ 
 * |_|\___/ \__, |\___/ \__,_|\__|
 *          |___/                 
 */

if(!isset($_COOKIE[$cookie_names["session"]]))
	senderror(new api_error(412, 8, "Failed to find session key cookie (you must already be logged out)."));

if(!isset($_COOKIE[$cookie_names["user"]]))
	senderror(new api_error(412, 9, "Failed to find username in cookie (you *may* already be logged out)."));

$sessions = getjson($paths["sessionkeys"]);
for($i = 0; $i < count($sessions); $i++)
{
	if($sessions[$i]->key == $_COOKIE[$cookie_names["session"]] and
	   $sessions[$i]->user == $_COOKIE[$cookie_names["user"]])
	{
		unset($sessions[$i]); //remove the session key
		$sessions = array_values($sessions); //reset all the values
		setjson($paths["sessionkeys"], $sessions); //save the sessions back to disk
		
		$response = new api_response(200, 0, "logout/success");
		sendjson($response);
		exit();
	}
}

senderror(new api_error(422, 11, "Failed to log out - Either your session key or username were invalid."));

?>

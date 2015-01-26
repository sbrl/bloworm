<?php
/*
 *       _               _    _             _       
 *   ___| |__   ___  ___| | _| | ___   __ _(_)_ __  
 *  / __| '_ \ / _ \/ __| |/ / |/ _ \ / _` | | '_ \ 
 * | (__| | | |  __/ (__|   <| | (_) | (_| | | | | |
 *  \___|_| |_|\___|\___|_|\_\_|\___/ \__, |_|_| |_|
 *                                    |___/         
 */

$response = new api_response(200, 0, "checklogin");
$response->logged_in = $logged_in;
if($logged_in)
{
	$response->user = $user;
	$response->isadmin = user_isadmin($user);
	$response->sessionkey = $_COOKIE[$cookie_names["session"]];
}
else
{
	$response->user = false;
	$response->isadmin = false;
	$response->sessionkey = false;
}
sendjson($response);
exit();

?>

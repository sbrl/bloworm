<?php
/*
 *  _             _       
 * | | ___   __ _(_)_ __  
 * | |/ _ \ / _` | | '_ \ 
 * | | (_) | (_| | | | | |
 * |_|\___/ \__, |_|_| |_|
 *          |___/         
 */

if($logged_in)
	senderror(new api_error(400, 129, "You are already logged in. Log out first and then try again."));

if(!isset($_POST["user"]) or !isset($_POST["pass"]))
	senderror(new api_error(449, 421, "No username and/or password was/were present in the body of the request.\n\nThe appropriate POST parameters are `user` and `pass`."));

if(!user_exists($_POST["user"]))
	senderror(new api_error(401, 128, "The username and/or password given was/were incorrect."));

try {
	$user_pass_hash = file_get_contents(user_dirname($_POST["user"]) . "password");
} catch(Exception $e)
{
	senderror(500, 703, "Failed to read in password hash.");
}

if(!password_verify($_POST["pass"], $user_pass_hash))
	senderror(new api_error(401, 128, "The username and/or password given was/were incorrect."));

// by this point we have verified that the user's credientials are correct

// rehash the password if necessary
if(password_needs_rehash(user_dirname($_POST["user"]) . "password", PASSWORD_DEFAULT, [ "cost" => $password_cost ]))
{
	file_put_contents(user_dirname($_POST["user"]) . "password", password_hash($_POST["pass"], PASSWORD_DEFAULT, [ "cost" => $password_cost ]));
}


$login_sessions = getjson($paths["sessionkeys"]);
$sessionkey = hash("sha256", openssl_random_pseudo_bytes($session_key_length));
$login_sessions[] = [
	"user" => utf8_encode($_POST["user"]),
	"key" => utf8_encode($sessionkey),
	"expires" => time() + $session_key_valid_time
];
setjson($paths["sessionkeys"], $login_sessions);

setcookie($cookie_names["user"], $_POST["user"], time() + $session_key_valid_time);
setcookie($cookie_names["session"], $sessionkey, time() + $session_key_valid_time);

http_response_code(200);
$response = new api_response(200, 0, "Login successful.");
$response->user = $_POST["user"];
$response->sessionkey = $sessionkey;
$response->expire_time = time() + $session_key_valid_time;
$response->isadmin = user_isadmin($_POST["user"]);
sendjson($response);
exit();

?>

<?php
/*
 *  _                 _                         _    _      _   
 * | |__   ___   ___ | | ___ __ ___   __ _ _ __| | _| | ___| |_ 
 * | '_ \ / _ \ / _ \| |/ / '_ ` _ \ / _` | '__| |/ / |/ _ \ __|
 * | |_) | (_) | (_) |   <| | | | | | (_| | |  |   <| |  __/ |_ 
 * |_.__/ \___/ \___/|_|\_\_| |_| |_|\__,_|_|  |_|\_\_|\___|\__|
 *                                                              
 */

$jscode = file_get_contents("js/bookmarklet.min.js");
// substitute in the missing pieces of the puzzle
$jscode = str_replace([
	"{user}",	// the user's name
	"{key}",	// the user's public key
	"{root}"	// the full url to your api.public.php
], [
	$user,
	user_getpublickey($user),
	// adapted from http://stackoverflow.com/a/1871778/1460422
	"http".(!empty($_SERVER['HTTPS'])?"s":"").
"://".$_SERVER['SERVER_NAME'].str_replace("api.php", "api.public.php", $_SERVER['REQUEST_URI']) 
], $jscode);

?>

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
	user_publickey($user),
	// adapted from http://stackoverflow.com/a/1871778/1460422
	$root_url . "api.public.php?action=bookmarklet&"
], $jscode);

echo("javascript:$jscode");
exit();

?>

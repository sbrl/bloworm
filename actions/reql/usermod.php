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

?>

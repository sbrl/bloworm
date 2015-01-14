<?php
/*
 *                  _                    
 *  _   _ _ __  ___| |__   __ _ _ __ ___ 
 * | | | | '_ \/ __| '_ \ / _` | '__/ _ \
 * | |_| | | | \__ \ | | | (_| | | |  __/
 *  \__,_|_| |_|___/_| |_|\__,_|_|  \___|
 *                                       
 */

if(isset($_GET["tags"]))
	$tags = explode(",", str_replace(", ", ",", $_GET["tags"]));
else
	senderror(new api_error(449, 506, "You didn't specify any `tags` to share.\n\nThe appropriate GET parameter is `tags`."));


http_response_code(501);
exit("This action is not implemented yet.");

?>

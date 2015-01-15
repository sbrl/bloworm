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

if(file_exists(get_user_data_dir_name($_GET["newusername"])))
	senderror(507, 151, "That user account already exists.");

http_response_code(501);
exit("This action is not implemented yet.");

?>

<?php
/*
 * Blow Worm API
 ***********************
 * HTTP status codes:
	* 422 - response understood *and* well formed, but validation failed
 * Actions:
	* login
		* username
		* password
	* logout
	* create
		* name
		* url
		* [optional] faviconurl
		* [optional] tags
	* delete
		* id
	* update
		* id
		* [optional] name
		* [optional] url
		* [optional] tags
		* [optional] faviconurl
	* search
		* query
	* 
 */

if(!isset($_GET["action"]))
	senderror(422, "No action was specified.");

switch($_GET["action"])
{
	case "login":
		break;
	
	case "logout":
		break;
	
	case "create":
		break;
	
	case "delete":
		break;
	
	case "update":
		break;
	
	case "search":
		break;
	
	default:
		senderror(404, "That `action` was not recognised.");
}
?>
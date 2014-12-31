<?php
/*
 * @summary class used to hold details about an error that has occurred.
 * 
 * @param $http_status - The http status code that should be sent
 * @param $code - The error code of the error that has occurred.
 * @param $message - The message that should be displayed.
 * 
 * @example new api_error(404, 345, "error #345: bookmark not found.");
 */
class api_error
{
	public $http_status = 400;
	public $code = 0;
	
	public $message = "An unknown error has occurred.";
	
	public function __construct($http_status, $code, $message)
	{
		$this->http_status = $http_status;
		$this->code = $code;
		
		$this->message = utf8_encode($message);
	}
}

/*
 * @summary sends an error message to the client
 * 
 * @param $api_error - an instance of api_error that contains the details of the error that has occurred.
 * 
 * @example senderror(new api_error(404, 345, "error #345: bookmark not found."));
 */
function senderror($api_error)
{
	http_response_code($api_error->http_status);
	header("content-type: application/json");
	exit("Error #$api_error->code: $api_error->message"); //todo convert this to json
}

/*
 * @summary Generates a brand new id, which can then be used to refer to a bookmark.
 * 
 * @returns A new id.
 */
function getid()
{
	//todo use `data/next.id`
	return hash("sha256", uniqid("", true)); //todo write this function
}


/*
 * @summary Hashes a password.
 * 
 * @returns The hashed password.
 */
function hash_password($password)
{
	return password_hash($password, PASSWORD_DEFAULT, [ "cost" => $password_cost ]);
}

/*
 * @summary Fetches and decodes a json file.
 * 
 * @param $filename - The path to the json file.
 * 
 * @returns The decoded json.
 */
function getjson($filename) { return json_decode(file_get_contents($filename)); }

/*
 * @summary Save something to a file as json.
 * 
 * @param $filename - The path to the file that should be written to.
 * @param $thing - The thing to save.
 */
function setjson($filename, $thing)
{
	if(!file_put_contents($filename, $thing))
		senderror(new api_error(507, 10, "Failed to save json to file $filename."));
}

/*
 * @summary Checks to see if a user exists.
 * 
 * @param $username - The username to check.
 * 
 * @returns Whether the user exists.
 */
function user_exists($user_to_check)
{
	global getjson;
	$userlist = getjson("data/userlist.json");
	foreach($userlist as $user_in_list)
	{
		if($user_to_check == $user_in_list)
			return true;
	}
	return false;
}
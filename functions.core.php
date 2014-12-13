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
	exit(json_encode($api_error, JSON_PRETTY_PRINT));
}


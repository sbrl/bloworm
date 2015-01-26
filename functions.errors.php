<?php
/*
 * @url http://php.net/manual/en/function.debug-backtrace.php#112238
 * @author jurchiks101 at gmail dot com
 * @summary Generates an easy to read stack trace.
 */
function generate_stack_trace()
{
    $e = new Exception();
    $trace = explode("\n", $e->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();
    
    for ($i = 0; $i < $length; $i++)
    {
        $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
    }
    
    return "\t" . implode("\n\t", $result);
}

/*
 * @summary class used to hold details about an error that has occurred.
 * 
 * @param $http_status - The http status code that should be sent.
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
	
	public function __construct($http_status, $code, $message, $details = false)
	{
		$this->http_status = $http_status;
		$this->code = $code;
		
		$this->message = utf8_encode($message);
		$this->details = $details;
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
	$response = [
		"http_status" => $api_error->http_status,
		"code" => $api_error->code,
		"message" => $api_error->message
	];
	if(isset($api_error->details))
		$response->details = $api_error->details;
	else
		$response->details = false;
	exit(json_encode($response, JSON_PRETTY_PRINT));
}

?>

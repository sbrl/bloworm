<?php
/*
 *  _                            _   
 * (_)_ __ ___  _ __   ___  _ __| |_ 
 * | | '_ ` _ \| '_ \ / _ \| '__| __|
 * | | | | | | | |_) | (_) | |  | |_ 
 * |_|_| |_| |_| .__/ \___/|_|   \__|
 *             |_|                   
 */

if(!in_array(strtolower($_SERVER['REQUEST_METHOD']), [ "put", "post" ]))
	senderror(new api_error(400, 530, "The import action only takes PUT or POST requests."));

if(isset($_GET["overwrite"]) and ($_GET["overwrite"] == "true" or $_GET["overwrite"] == "yes"))
	$overwrite = true;
else
	$overwrite = false;

$request_headers = getallheaders();
$request_headers = array_change_key_case($request_headers);
header("content-type: application/json");
var_dump($request_headers);

if(!isset($request_headers["content-length"]))
	senderror(new api_error(411, 531, "No content-length header was present in the request."));

if($request_headers["content-length"] > 10e6)
	senderror(new api_error(413, 532, "You tried to send too much data to the server. Please contact the administrator of this bloworm installation to tget your data imported manually."));

$data = file_get_contents("php://input");
$data = gzdecode($data);
if(!$data)
	senderror(new api_error(400, 533, "An error occurred whilst trying to decompress the data you sent."));

try {
	$to_import = json_decode($data);
} catch(Exception $error) {
	senderror(new api_error(400, 534, "An error occurred while parsing the data you sent."));
}

// todo unify this so that the list of files that a user has are in one master array
// note a similar array is defined in the export action
$filenames = [
	"bookmarks.json",
	"tags.json"
];
foreach($to_import as $key => $value)
{
	if(!in_array($key, $filenames))
		continue; // this filename isn't part of the user's data, leave it alone
	
	if($overwrite === true)
		setjson(user_dirname($user) . $key, $value);
	else
	{
		// todo merge the existing file with the new one
		http_response_code(501);
		exit("Non overwriting imports are not implemented yet.");
	}
};

$response = new api_response(201, 0, "import/" . ($overwrite ? "overwrite" : "non-overwrite") . "/success");
sendjson($response);

?>

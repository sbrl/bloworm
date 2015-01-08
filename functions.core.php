<?php
/*
 * @summary base class used to contruct api responses.
 * 
 * @param $http_status - The http status code that should be sent.
 * @param $code - An indicator code giving a little more information about the response.
 * @param $message - The message that should be displayed.
 * 
 * @example new api_error(404, 345, "error #345: bookmark not found.");
 */
class api_response
{
	public $http_status = 200;
	public $code = 0;
	public $type = "";
	
	public function __construct($http_status, $code, $type)
	{
		$this->http_status = $http_status;
		$this->code = $code;
		
		$this->type = utf8_encode($type);
	}
}

/*
 * @summary Sends something to the client after encoding it as json. Unlike senderror, this function does not terminate the request. Also sets the correct header to tell the client that it will be getting a lump of json as a response.
 * 
 * @param $thing - The thing to send.
 */
function sendjson($thing)
{
	header("content-type: application/json");
	echo(json_encode($thing, JSON_PRETTY_PRINT));
}

/*
 * @summary Generates a brand new id, which can then be used to refer to a bookmark.
 * 
 * @returns A new id.
 */
function getid()
{
	//todo use `data/next.id`
	return utf8_encode(hash("sha256", uniqid("", true)));
}


/*
 * @summary Hashes a password.
 * 
 * @returns The hashed password.
 */
function hash_password($password)
{
	global $password_cost;
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
	if(!file_put_contents($filename, json_encode($thing)))
		senderror(new api_error(507, 10, "Failed to save json to file $filename."));
}

/*
 * @summary Use levenshtein's distance to sort an array of bookmarks based on a query string.
 * 
 * @param $query - The query string
 * @param $haystack = The array of objects to sort.
 * 
 * @returns The sorted array.
 */
function fuzzy_search_bookmarks($query, $haystack)
{
	$ranked = [];
	foreach($haystack as $elem)
	{
		$best_so_far = INF;
		foreach($elem as $key => $value)
		{
			// get the similarity between the key and the query string
			$new_score = levenshtein($query, $elem->$key, 1, 1, 0);
			// this score is lower than the best, update the best
			if($new_score < $best_so_far)
				$best_so_far = $new_score;
		}
		//todo insert into the correct place instead of sorting afterwards
		$ranked[] = [
			"rank" => $best_so_far,
			"elem" => $elem
		];
	}
	
	usort($ranked, function($a, $b) {
		return $a->rank < $b->rank;
	});
	
	$result = [];
	foreach($ranked as $item)
	{
		$result[] = $item->elem;
	}
	return $result;
}
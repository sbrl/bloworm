<?php
/*
 * @summary Validates a url.
 * 
 * @param $url - The url to validate
 * 
 * @returns Whether the url is valid.
 */
function validate_url($url)
{
	if(preg_match("/^([a-z]+)\:\/\/([a-z\.]+)/i", $url) === 0)
		return false;
	
	return true;
}

/*
 * @summary Checks the status code of a url to make sure that the url actually exists
 * 
 * @param $url - The url to test.
 * 
 * @returns Whether ther url gives a 2xx code.
 */
function test_url_status($url)
{
	$status = intval(explode(" ", get_headers($url)[0])[1]);
	if(!($status >= 400 and $status < 500))
		return true;
	else
		return false;
}

/*
 * @summary Follows a chain of redirects and returns that last url in the sequence.
 *
 * @param $url - The url to start at.
 * @param $maxdepth - The maximum depth to which to travel following redirects.
 *
 * @returns The url at the end of the redirect chain.
 */
function follow_redirects($url, $maxdepth = 10, $depth = 0)
{
	//return the current url if we have hit the maximum depth
	if($depth >= $maxdepth)
		return $url;

	//download the headers from the url and make all the keys lowercase
	try {
		$headers = @get_headers($url, true);
	} catch (Exception $e) { // catch the errors
		senderror(new api_error(502, 714, "Failed to fetch the headers from url: $url (error occurred)"));
	}
	// catch the warnings
	if($headers === false)
		senderror(new api_error(502, 714, "Failed to fetch the headers from url: $url (warning / other occurred)"));
	
	$headers = array_change_key_case($headers);
	//we have a redirect if the `location` header is set
	if(isset($headers["location"]))
	{
		return follow_redirects($headers["location"], $maxdepth, $depth + 1);
	}
	else
	{
		return $url;
	}
}

/*
 * @summary Given a url, this function will attempt to work out an appropriate name for it.
 *
 * @param $url - The url to analyse.
 *
 * @returns An appropriate name for the bookmark.
 */
function auto_find_name($url)
{
	global $default_bookmark_name;
	
	if(!validate_url($url))
		senderror(new api_error(400, 521, "The url you specified was invalid."));
	
	// todo prevent downloading of large files
	
	// todo send HEAD request instead of GET request
	try {
		$headers = get_headers($url, true);
	} catch (Exception $e) {
		senderror(new api_error(502, 712, "Failed to fetch the headers from url: $url"));
	}
	$headers = array_change_key_case($headers);
	$title = $default_bookmark_name;
	
	$content_type = $headers["content-type"];
	if(!is_string($content_type)) // account for arrays of content types
		$content_type = $content_type[0];
	
	if(strpos($content_type, "text/html") !== false)
	{
		//the url refers to some html
		try {
			$html = @file_get_contents($url); // todo make this less hackish....
		} catch (Exception $e) {
			senderror(new api_error(502, 713, "Failed to download url: $url"));
		}
		$matches = [];
		preg_match("/<title>([^>]*)<\/title>/i", $html, $matches);
		
		if(count($matches) >= 2)
			$title = trim($matches[1]);
	}

	return $title;
}

/*
 * @summary Given a url, this function will attempt to find it's correspending favicon.
 *
 * @returns The url of the corresponding favicon.
 */
function auto_find_favicon_url($url)
{
	if(!validate_url($url))
		senderror(new api_error(400, 520, "The url you specified for the favicon was invalid."));
	
	// todo protect against downloading large files
	// todo send HEAD request instead of GET request
	try {
		$headers = get_headers($url, true);
	} catch (Exception $e) {
		senderror(new api_error(502, 710, "Failed to fetch the headers from url: $url"));
	}
	$headers = array_change_key_case($headers);
	
	$urlparts = [];
	preg_match("/^([a-z]+)\:(?:\/\/)?([^\/?#]+)(.*)/i", $url, $urlparts);
	
	$content_type = $headers["content-type"];
	if(!is_string($content_type)) // account for arrays of content types
		$content_type = $content_type[0];
	
	$faviconurl = "images/favicon-default.png";
	if(strpos($content_type, "text/html") !== false)
	{
		try {
			$html = @file_get_contents($url); // todo make this less hackish....
		} catch (Exception $e) {
			senderror(new api_error(502, 711, "Failed to fetch url: $url"));
		}
		$matches = [];
		if(preg_match("/rel=\"shortcut(?: icon)?\" (?:href=[\'\"]([^\'\"]+)[\'\"])/i", $html, $matches) === 1)
		{
			$faviconurl = $matches[1];
			// make sure that the favicon url is absolute
			if(preg_match("/^[a-z]+\:(?:\/\/)?/i", $faviconurl) === 0)
			{
				// the url is not absolute, make it absolute
				$basepath = dirname($urlparts[3]);
				
				// the path should not include the basepath if the favicon url begins with a slash
				if(substr($faviconurl, 0, 1) === "/")
				{
					$faviconurl = "$urlparts[1]://$urlparts[2]$faviconurl";
				}
				else
				{
					$faviconurl = "$urlparts[1]://$urlparts[2]$basepath/$faviconurl";
				}
			}
		}
	}
	
	if($faviconurl == "images/favicon-default.png")
	{
		// we have not found the url of the favicon yet, parse the url
		// todo guard against invalid urls
		
		$newfaviconurl = "$urlparts[1]://$urlparts[2]/favicon.ico";
		$newfaviconurl = follow_redirects($newfaviconurl);
		
		if(test_url_status($newfaviconurl))
			return $newfaviconurl;
	}
	
	return $faviconurl;
}
?>

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
	if(!preg_match("/^([a-z]+)\:\/\/([a-z\.]+)/i", $url))
		return false;
	
	return true;
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
	$headers = get_headers($url, true);
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
	// todo catch errors thrown by `get_headers()` and `file_get_contents()`
	
	// todo send HEAD request instead of GET request
	$headers = get_headers($url, true);
	$headers = array_change_key_case($headers);
	$title = $default_bookmark_name;
	
	$content_type = $headers["content-type"];
	if(!is_string($content_type)) // account for arrays of content types
		$content_type = $content_type[0];
	
	if(strpos($content_type, "text/html") !== false)
	{
		//the url refers to some html
		$html = file_get_contents($url);
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
	// todo catch errors thrown by `get_headers()` and `file_get_contents()`
	// todo send HEAD request instead of GET request
	$headers = get_headers($url, true);
	$headers = array_change_key_case($headers);
	
	$urlparts = [];
	preg_match("/^([a-z]+)\:(?:\/\/)?([^\/?#]+)(.*)/i", $url, $urlparts);
	
	$content_type = $headers["content-type"];
	if(!is_string($content_type)) // account for arrays of content types
		$content_type = $content_type[0];

	$faviconurl = "images/favicon-default.png";
	if(strpos($content_type, "text/html") !== false)
	{
		$html = file_get_contents($url);
		$matches = [];
		if(preg_match("/rel=\"shortcut(?: icon)?\" (?:href=[\'\"]([^\'\"]+)[\'\"])/i", $html, $matches) === 1)
		{
			$faviconurl = $matches[4];
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
		
		$faviconurl = "$urlparts[1]://$urlparts[2]/favicon.ico";
		$faviconurl = follow_redirects($faviconurl);
		$favheaders = get_headers($faviconurl, true);
		$favheaders = array_change_key_case($favheaders);
		
		if(preg_match("/2\d{3}/i", $favheaders[0]) === 0)
			return $faviconurl;
	}
	
	return $faviconurl;
}
?>

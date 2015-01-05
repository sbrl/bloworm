<?php
/*
 * @summary Returns the path to a given user's data directory
 * 
 * @param $username - The user's name
 * 
 * @returns The path to the given user's data directory
 */
function get_user_data_dir_name($username)
{
	return "./data/users/$username/";
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
	//changes commented out getjson
	//global getjson;
	$userlist = getjson("data/userlist.json");
	foreach($userlist as $user_in_list)
	{
		if($user_to_check == $user_in_list)
			return true;
	}
	return false;
}

?>

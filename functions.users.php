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
	return "./data/$username/";
}


?>

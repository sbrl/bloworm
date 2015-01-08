<?php
/*
 *      _      _      _       
 *   __| | ___| | ___| |_ ___ 
 *  / _` |/ _ \ |/ _ \ __/ _ \
 * | (_| |  __/ |  __/ ||  __/
 *  \__,_|\___|_|\___|\__\___|
 *                            
 */

if(isset($_GET["id"]))
	$id_to_delete = $_GET["id"];
else
	senderror(new api_error(449, 18, "You didn't specify an `id` to delete.\n\nThe appropriate GET parameter is `id`."));

http_response_code(501);
exit("This action is not implemented yet.");

?>

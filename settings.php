<?php
// The cost used when hashing passwords via `password_hash()`.
$password_cost = 10;

// The length of raw session keys in bits. Session keys are hashed,
// so the session keys themselves are always the same length.
$session_key_length = 128;

// The length of time in seconds that a session key is valid.
$session_key_valid_time = 60*60*24*30; //default: 30 days

// An associative array containing the names of all the cookies
// that are used by blow worm.
$cookie_names = [
	"session" => "blow-worm-session", //stores the session key
	"user" => "blow-worm-user" //stores the username
];

// the default name for bookmarks that don't have a name.
$default_bookmark_name = "(untitled)";

// The maximum size that blow worm will download when downloading
// any kind of file in bytes.
$max_download_size = 204800; //200kb
?>

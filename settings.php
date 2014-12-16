<?php
// The cost used when hashing passwords via `password_hash()`.
$password_cost = 10;

// The length of raw session keys in bits. Session keys are hashed,
// so the session keys themselves are always the same length.
$session_key_length = 128;

// The length of time in seconds that a session key is valid.
$session_key_valid_time = 60*60*24*30; //default: 30 days
?>

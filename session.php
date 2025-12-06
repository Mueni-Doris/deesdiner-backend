<?php
// session.php

$path = __DIR__ . "/sessions";
if (!is_dir($path)) {
    mkdir($path, 0777, true);
}
session_save_path($path);

// Let PHP set domain automatically (don’t force "localhost")
session_set_cookie_params([
    "lifetime" => 0,
    "path"     => "/",
    "secure"   => false,   // must be false on localhost (no HTTPS)
    "httponly" => true,
    "samesite" => "Lax"    // "Lax" works fine in dev, prevents cookie drop
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

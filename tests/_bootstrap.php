<?php
	// This is global bootstrap for autoloading
	require_once(__DIR__ . "/../src/classes/Autoloader.php");
    require_once(__DIR__ . "/../src/vendor/autoload.php");
    require_once(__DIR__ . "/../src/bootstrap/registry.php");
    require_once(__DIR__ . "/../src/bootstrap/global.php");

    $_SERVER["REMOTE_ADDR"] = "127.0.0.1";
<?php	
	//Register the autoloaders.
	require_once(__DIR__ . "/classes/Autoloader.php"); //All NYKC classes.
	require_once(__DIR__ . "/vendor/autoload.php"); //third party items like Smarty.

	//Bootstrap the app.
	$app = require_once(__DIR__ . '/bootstrap/start.php');
	
	//Finally, run the app to display the site.
	$app->run();

<?php
	
	/*
	|---------------------
	| Now You Know Central
	| --------------------
	|
	| This is the core of the NYKC site. Anything neccesary for loading the application itself 
	| and bootstrap settings are done here before the app itself starts.
	| Requests for pages using the action and subaction parameters come through here and the 
	| appropriate action is taken depending on those values.
	|
	*/
	
	//Register the autoloaders.
	require_once(__DIR__ . "/classes/Autoloader.php"); //All NYKC classes.
	require_once(__DIR__ . "/vendor/autoload.php"); //third party items like Smarty.

	//Bootstrap the app.
	$app = require_once(__DIR__ . '/bootstrap/start.php');
	
	//Finally, run the app to display the site.
	$app->run();
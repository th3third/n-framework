<?php
	// Here you can initialize variables that will be available to your tests
	require_once(__DIR__ . "/../../src/bootstrap/registry.php");
	require_once(__DIR__ . "/../commons/TestCommons.php");

	ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
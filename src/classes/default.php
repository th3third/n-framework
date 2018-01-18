<?php

	/*
	|------------------------------------------------
	| default.php
	|------------------------------------------------
	| 
	| !!!!! DO NOT CHANGE THIS FILE FOR A LOCAL ENVIRONMENT !!!!!
	|
	| Use the config/local_settings.php file to override these values.
	|
	*/

	return array(

		/*
		|------------------------------------------------
		| Database connection settings
		|------------------------------------------------
		*/

		, 'DB_TYPE' => 'mysqli'
		, 'DB_HOST' => 'localhost'
		, 'DB_PORT' => '8306'
		, 'DB_DATABASE' => 'OVERRIDE ME'
		, 'DB_USERNAME' => 'OVERRIDE ME'
		, 'DB_PASSWORD' => 'OVERRIDE ME'

		/*
		|------------------------------------------------
		| Log settings
		|------------------------------------------------
		*/

		, 'LOG_DIR' => 'log/'
		, 'LOG_REPORT_LEVEL' => 'error'
		, 'LOG_DEFAULT_LINE_FORMAT' => "{time} [{ip}] (user {user_id}) {message}\n"
		, 'LOG_DEFAULT_TIME_FORMAT' => '%H:%M:%S'	

		/*
		|------------------------------------------------
		| Smarty settings
		|------------------------------------------------
		*/

		, 'SMARTY_CACHE_DIR' => __DIR__ . '/../cache/'
		, 'SMARTY_TEMPLATE_DIR' => __DIR__ . '/../templates/'
		, 'SMARTY_COMPILED_DIR' => __DIR__ . '/../templates/compiled/'
		, 'SMARTY_PLUGINS_DIR' => __DIR__ . '/../plugins/'
		, 'SMARTY_CACHING' => Smarty::CACHING_OFF
		, 'SMARTY_FORCE_COMPILE' => false
		, 'SMARTY_COMPILE_CHECK' => false
		, 'SMARTY_MERGE_COMPILED_INCLUDES' => true
		, 'SMARTY_PHP_HANDLING' => 0
		, 'SMARTY_ERROR_REPORTING' => 0
		, 'SMARTY_AUTOLOAD_FILTERS' => [
			"pre" => [
				"register"
			]
		]
	);
<?php

	spl_autoload_register(function($class) 
	{
		$folders = array(
			""
			, DIRECTORY_SEPARATOR . "actions"
			, DIRECTORY_SEPARATOR . "helpers"
			, DIRECTORY_SEPARATOR . "models"
			, DIRECTORY_SEPARATOR . "services"
			, DIRECTORY_SEPARATOR . "interfaces"
		);

		foreach ($folders as $dir)
		{
			$file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . $dir . DIRECTORY_SEPARATOR . $class . '.php';

			if (file_exists($file)) 
			{
	        	require_once $file;
	        	return;
			}
		}
	});
<?php

	/**
	* @class Config
	*/

	class Config
	{
		private static $_constants;

		const CONFIG_DIR = "config";

		public static function getConstants()
		{
			if (!is_array(self::$_constants))
			{
				self::setEnvironment(null);
			}

			return self::$_constants;
		}

		private static function setConstants($constants)
		{
			self::$_constants = $constants;
		}

		public static function get($name, $default = null)
		{
			$constants = self::getConstants();

			$names = explode(".", $name);

			$value = null;
			foreach ($names as $name)
			{
				if (!isset($value[$name]) && !isset($constants[$name]))
				{
					return $default;
				}

				$value = !is_null($value) ? $value[$name] : $constants[$name];
			}

			return !is_null($value) ? $value : $default;
		}

		public static function getAll()
		{
			return self::getConstants();
		}

		public static function all()
		{
			return self::getAll();
		}

		public static function setEnvironment($environment = null)
		{
			//Defaults.
			$constants = require(__DIR__ . "/default.php");

			//Environment specific stuff.
			if (!empty($environment))
			{
				$dir = __DIR__ . "/../" . self::CONFIG_DIR . "/" . $environment . "/";

				$di = new DirectoryIterator($dir);
				foreach ($di as $file)
				{
					if (!$file->isDir() && !$file->isLink() && !$file->isDot())
					{
						$constants = array_merge($constants, require($file->getPathName()));
					}
				}
			}

			//Local settings (if applicable.)
			$localSettings = include(__DIR__ . "/../" . self::CONFIG_DIR . "/local_settings.php");

			if (isset($localSettings) && is_array($localSettings))
			{
				$constants = array_merge($constants, $localSettings);
			}

			self::setConstants($constants);

			return true;
		}
	}
<?php

	/**
	* @class Logger
	*/

	class Logger
	{
		private $log_filename;
		private $logger;
		private $keywords = array ( '{time}', '{level}', '{top_stack}', '{message}', '{id}' );
		public $config=array();
		private $start_msec = 0;
		private $file_handle;

		public $levels = array ( 'trace' => 0, 'debug' => 1, 'info' => 2, 'warn' => 3, 'error' => 4, 'fatal' => 5 );

		function Logger ($config = array()) // Constructor
		{
			if ( $this->start_msec == 0 )
			$this->start_msec = microtime(true);

			if ( ($config == null) || ( ! isset ( $config['id'])))  {
				$config['id'] = basename(__FILE__);
			}

			if ( ($config == null) || ( ! isset ( $config['log_dir'])))  {
				$config['log_dir'] = __DIR__ . "/log/";
			}

			if ( ($config == null) || ( ! isset ( $config['line_format'])))  {
				$config['line_format'] = "{time}.{elapsed_msec} {id} {level} {message}\n";
			}

			if ( ($config == null) || ( ! isset ( $config['time_format']) ))  {
				$config['time_format'] = "%H:%M:%S";
			}

			if ( ($config == null ) || ( !isset ( $config['report_level']) )) {
				$config['report_level'] = "error";
			}

			if ( ($config == null ) || ( !isset ( $config['user_id']) )) {
				$config['user_id'] = "guest";
			}

			$this->log_filename = $config['log_dir'] . strftime ( '%Y-%m-%d' . '_log.txt' );

			$this->config = $config;
		}

		public function trace($msg)
		{
			return $this->log($msg, 'trace');
		}

		public function debug($msg)
		{
			return $this->log($msg, 'debug');
		}

		public function info($msg)
		{
			return $this->log($msg, 'info');
		}

		public function warn($msg)
		{
			return $this->log($msg, 'warn');
		}

		public function error($msg)
		{
			return $this->log($msg, 'error');
		}

		public function fatal($msg)
		{
			return $this->log($msg, 'fatal');
		}

		function getReportLevel()
		{
			return $this->config['report_level'];
		}

		function validLogLevel($level)
		{
			return $this->levels[$level] >= $this->levels[$this->config['report_level']];
		}

		function getFilename ()
		{
			return $this->log_filename;
		}

		function isDebugEnabled()
		{
			return ( $this->config['report_level'] <= $this->levels['debug']);
		}

		function isTraceEnabled()
		{
			return ( $this->config['report_level'] <= $this->levels['trace']);
		}

		public function callStack($stacktrace) 
		{
			$output = '';
			$count = count($stacktrace);
			$i = 1;
		    foreach($stacktrace as $node) 
		    {
		    	$output .= "$i. ";
		    	if (isset($node['file']))
					$output .= basename($node['file']) . ":";
				
				if (isset($node['function']))
					$output .= $node['function'];
					
				if (isset($node['line']))
					$output .= "(" .$node['line'].")";
					
				if ($i < $count)
				{
					$output .= "\n";
				}
				
		        $i++;
		    }
			
			return $output;
		}

		public function getStackTrace()
		{
			$stackTrace = debug_backtrace();
			$count      = count($stackTrace);
			for ($i = 0; $i < $count; $i++)
			{
				if (isset($stackTrace[$i]['file']) && $stackTrace[$i]['file'] === __FILE__)
				{
					unset($stackTrace[$i]);

					break;
				}
			}

			return $stackTrace;
		}

		public function getStackTraceTop()
		{
			$stack = $this->getStackTrace();
			do
			{
				$top = array_shift($stack);
			}
			while ($top["class"] == get_class() && count($stack) > 0);

			return $top;
		}

		public function printStackTrace($type = "trace")
		{
			switch($type)
			{
				case 'trace':
				case 'debug':
				case 'info':
				case 'warn':
				case 'error':
				case 'fatal':
					$this->log($this->callStack($this->getStackTrace()), $type);
					break;
				default:
					return false;
					break;
			}

			return true;
		}

		function formatOutput ( $msg, $level_name)
		{
			$top = $this->getStackTraceTop();
			$output = $this->config['line_format'];
			$output = str_replace ('{time}', strftime ( $this->config['time_format']), $output );
			$output = str_replace ('{user_id}', $this->config['user_id'], $output);
			$output = str_replace ('{elapsed_msec}', sprintf ( '%d', (microtime(true) - $this->start_msec) * 1000) , $output );
			$output = str_replace ('{level}', str_pad(strtoupper($level_name), 5, " ", STR_PAD_BOTH), $output );
			$output = str_replace ('{file}', isset($top["file"]) ? basename($top["file"]) : "NO FILE", $output);
			$output = str_replace ('{line}', isset($top["line"]) ? $top["line"] : "NO LINE", $output);
			$output = str_replace ('{function}', $top["function"], $output);
			$output = str_replace ('{class}', $top["class"], $output);
			$output = str_replace ('{id}', $this->config['id'], $output );
			$output = str_replace ('{ip}', str_pad($_SERVER['REMOTE_ADDR'], 15, ' ', STR_PAD_RIGHT), $output);
			$output = str_replace ('{message}', $msg, $output );

			return $output;
		}

		function log ($msg, $level_name='info')
		{
			if (!$this->validLogLevel($level_name))
			{
				return false;
			}

			if (!isset($this->file_handle))
			{
				$this->file_handle = fopen($this->log_filename, 'a');
			}

			$s = $this->formatOutput ( $msg, $level_name);

			$bytes_written = fwrite($this->file_handle, $s);

			if ($bytes_written === false)
			{
				return false;
			}

			return $this;
		}

		function Close()
		{
			return fclose ( $this->file_handle );
		}

		/**
		* @method getLogListing
		* @return {Array}
		*/
		public function getLogListing()
		{
			$listing = array_diff(scandir($this->config["log_dir"]), ['.', '..']);
			rsort($listing, SORT_NATURAL | SORT_FLAG_CASE);

			return $listing;
		}

		/**
		* @method getLogFile
		* @param {String} name
		* @return {Resource} file
		*/
		public function getLogFile($name = null)
		{
			$path = $this->config["log_dir"];
			if ($name === null)
			{
				$path .= $this->log_filename;
			}

			$path .= $name;

			return fread(fopen($path, "r"), filesize($path));
		}
	}
<?php

	/**
	* @class Response
	*/

	class Response
	{
		public $type;
		public $contents;

		public static function json($contents)
		{
			$instance = new static;

			$instance->type = "json";
			$instance->contents = json_encode($contents);

			return $instance;
		}

		public static function raw($contents)
		{
			$instance = new static;

			$instance->type = "raw";
			$instance->contents = $contents;

			return $instance;
		}

		public static function redirect($url)
		{
			$instance = new static;

			$instance->type = "redirect";
			$instance->url = $url;

			return $instance;
		}

		public static function iframe($url)
		{
			$instance = new static;

			$instance->type = "iframe";
			$instance->url = $url;

			return $instance;
		}

		public static function page($url)
		{
			$instance = new static;

			$instance->type = "page";

			$options = array(
		        CURLOPT_RETURNTRANSFER => true     // return web page
		  		, CURLOPT_HEADER => false    // don't return headers
		        , CURLOPT_FOLLOWLOCATION => true     // follow redirects
		        , CURLOPT_ENCODING => ""       // handle all encodings
		        , CURLOPT_USERAGENT => "spider" // who am i
		        , CURLOPT_AUTOREFERER => true     // set referer on redirect
		        , CURLOPT_CONNECTTIMEOUT => 120      // timeout on connect
		        , CURLOPT_TIMEOUT => 120      // timeout on response
		        , CURLOPT_MAXREDIRS => 10       // stop after 10 redirects
		        , CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
		        , CURLOPT_FAILONERROR => false
		    );

		    $ch = curl_init($url);
		    curl_setopt_array($ch, $options);
		    $instance->contents = curl_exec($ch);
		    $err = curl_errno($ch);
		    $errmsg = curl_error($ch);
		    $header = curl_getinfo($ch);
		    curl_close($ch);

			return $instance;
		}

		public static function file($name, $url)
		{
			header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename="' . $name . '"');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($url));
		    readfile($url);
		    exit();
		}
	}
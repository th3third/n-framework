<?php

	use Omnipay\Omnipay;

	/*
	|---------------------------------------------------
	| @class App
	|------------------------------------------------
	| 
	| The master App class that runs everything in NYKC.
	|
	*/

	class App
	{
		protected $environment;
		protected $logger;
		protected static $_smarty;
		protected static $_runningDir;
		
		/**
		* @method detectEnvironment
		* @param {Array} environments
		* @return {Boolean}
		*/
		public function detectEnvironment($environments)
		{
			$serverName = gethostname();
			
			$this->environment = null;
			
			foreach ($environments as $key => $environment)
			{
				foreach ($environment as $hostname)
				{
					if ($hostname == $serverName)
					{
						$this->environment = $key;
						
						return true;
					}
				}
			}

			return false;
		}

		/**
		* @method getEnvironment
		* @return {String}
		*/
		public function getEnvironment()
		{
			return $this->environment;
		}

		/**
		* @method getSmarty
		* @return {Smarty}
		*/
		public static function getSmarty()
		{
			if (!(self::$_smarty instanceof Smarty))
			{
				$_smarty = new NYKSmarty();
				$_smarty->cache_dir = Config::get('SMARTY_CACHE_DIR');
				$_smarty->compile_dir = Config::get('SMARTY_COMPILED_DIR');
				$_smarty->template_dir = Config::get('SMARTY_TEMPLATE_DIR');
				$_smarty->addPluginsDir(Config::get('SMARTY_PLUGINS_DIR'));
				$_smarty->setCaching(Config::get('SMARTY_CACHING'));
				$_smarty->force_compile = Config::get('SMARTY_FORCE_COMPILE');
				$_smarty->compile_check = Config::get("SMARTY_COMPILE_CHECK");
				$_smarty->merge_compiled_includes = Config::get("SMARTY_MERGE_COMPILED_INCLUDES");

				self::$_smarty = $_smarty;
			}

			return self::$_smarty;
		}

		/**
		* @method getGateway
		* @param {String} gateway
		* @return {AbstractGateway} Omnipay gateway of specified type.
		*/
		public static function getGateway($gateway = "Payflow")
		{
			switch (strtolower($gateway))
			{
				default:
				case "payflow":
				case "payflowextended";
					$gateway = Omnipay::create("PayflowExtended");
					$gateway->setTestMode(Config::get("PAY_ONLINE_TESTING"));
					$gateway->setUsername(Config::get("PAY_ONLINE_PAYFLOW_USER"));
			        $gateway->setPassword(Config::get("PAY_ONLINE_PAYFLOW_PASSWORD"));
			        $gateway->setVendor(Config::get("PAY_ONLINE_PAYFLOW_VENDOR"));
			        $gateway->setPartner(Config::get("PAY_ONLINE_PAYFLOW_PARTNER"));
					break;

				case "paypal";
					$gateway = Omnipay::create("PayPal");

					$gateway->setTestMode(Config::get("PAY_ONLINE_TESTING"));
					if (Config::get("PAY_ONLINE_TESTING"))
					{
						$gateway->setUsername(Config::get("PAYPAL_API_TESTING_USER"));
				        $gateway->setPassword(Config::get("PAYPAL_API_TESTING_PASSWORD"));
				        $gateway->setSignature(Config::get("PAYPAL_API_TESTING_SIGNATURE"));
					}
					else
					{
						$gateway->setUsername(Config::get("PAYPAL_API_USER"));
				        $gateway->setPassword(Config::get("PAYPAL_API_PASSWORD"));
				        $gateway->setSignature(Config::get("PAYPAL_API_SIGNATURE"));
				    }
					break;

				case "paypalexpress":
					$gateway = Omnipay::create("PayPal_Express");

					$gateway->setTestMode(Config::get("PAY_ONLINE_TESTING"));
					if (Config::get("PAY_ONLINE_TESTING"))
					{
						$gateway->setUsername(Config::get("PAYPAL_API_TESTING_USER"));
				        $gateway->setPassword(Config::get("PAYPAL_API_TESTING_PASSWORD"));
				        $gateway->setSignature(Config::get("PAYPAL_API_TESTING_SIGNATURE"));
					}
					else
					{
						$gateway->setUsername(Config::get("PAYPAL_API_USER"));
				        $gateway->setPassword(Config::get("PAYPAL_API_PASSWORD"));
				        $gateway->setSignature(Config::get("PAYPAL_API_SIGNATURE"));
				    }
					break;
			}

	        return $gateway;
		}

		/**
		* @method getBugsnag
		* @return {Bugsnag}
		*/
		public function getBugsnag()
		{
			if (!$this->bugsnag)
			{
				$this->bugsnag = Bugsnag\Client::make(Config::get("BUGSNAG_API_KEY"));
				$this->bugsnag->setAppType("Central");
				$this->bugsnag->setAppVersion(Config::get("NYKC2_VERSION"));
				$this->bugsnag->setReleaseStage($this->environment);
				$this->bugsnag->registerCallback(function ($report) {
				    $report->setUser([
				        "id" => "-1"
				        , "name" => "Guest"
				    ]);
				});
				Bugsnag\Handler::register($this->bugsnag);
			}

			return $this->bugsnag;
		}

		/**
		* The method to run should an error occur.
		* @method error
		* @param {Integer} number
		* @param {String} string
		* @param {String} file
		* @param {Integer} line
		*/
		public function error($number, $string, $file, $line)
		{
			$message = "";

			switch ($number) 
			{
			    case E_USER_ERROR:
			    	$type = "Error";
			    	MessageBag::add("debug", $string);
			    	break;
			    case E_ERROR:
			    	$type = "Error";
			        $message .= "$string on line $line in file $file";
			        $this->logger->error($message);
			        exit(1);
			        break;

			    case E_USER_WARNING:
			    	$type = "Warn";
			    	MessageBag::add($type, $string);
			    	break;
			    case E_WARNING:
			    	$type = "Warn";
			    	$message .= "$string on line $line in file $file";
			        $this->logger->warn($message);
			        MessageBag::add("debug", var_export($message, true));
			        break;

			    case E_USER_NOTICE:
			    	$type = "Notice";
			    	MessageBag::add($type, $string);
			    	break;
			    case E_NOTICE:
			    	$type = "Notice";
			    	$message .= "$string on line $line in file $file";
			        $this->logger->info($message);
			        MessageBag::add("debug", var_export($message, true));
			        break;

			    case E_STRICT:
			    	$type = "Strict";
			    	$message .= "$string on line $line in file $file";
			        $this->logger->debug($message);
			        MessageBag::add("debug", var_export($message, true));
			    	break;

			    case E_DEPRECATED:
			    	$type = "Deprecated";
			    	$message .= "$string on line $line in file $file";
			        $this->logger->debug($message);
			        MessageBag::add("debug", var_export($message, true));
			    	break;

			    default:
			    	$type = "Unknown";
			        $message .= "Unknown error type: [$number] $string";
			        $this->logger->warn($message);
			        MessageBag::add("debug", var_export($message, true));
			        break;
		    }

		    if (Config::get("FEATURE_FLAG.BUGSNAG"))
			{
		    	$this->getBugsnag()->notifyError($type, $message);
		    }
		}

		/**
		* @method shutdown
		*/
		public function shutdown()
		{
			if (!$error = error_get_last()) 
			{
				return;
			}

			$fatals = array(
				E_USER_ERROR => 'Fatal Error',
				E_ERROR => 'Fatal Error',
				E_PARSE => 'Parse Error', 
				E_CORE_ERROR => 'Core Error',
				E_CORE_WARNING => 'Core Warning',
				E_COMPILE_ERROR => 'Compile Error',
				E_COMPILE_WARNING => 'Compile Warning'
			);

			$file = $error["file"];
	    	$type = $error["type"];
	    	$line = $error["line"];
	    	$string = $error["message"];

	    	$message = "";
	    	$message .= "$string on line $line in file $file";

			$this->logger->fatal($message);

			if (Config::get("FEATURE_FLAG.BUGSNAG"))
			{
				$this->getBugsnag()->notifyError("Shutdown", $message);
			}

			if (isset($fatals[$error['type']])) 
			{
				$_smarty = App::getSmarty();

				if ($this->environment == "development")
				{
			    	$_smarty->assign("message", $message);

			    	print($_smarty->fetch("error_shutdown.tpl"));
			    }
			    else
			    {
					print($_smarty->fetch("error.tpl"));
			    }

		        die();
		    }
		}

		/**
		* The method to run should an exception occur.
		* @method exception
		* @param {Exception} exception
		*/
		public function exception(Exception $exception)
		{
			if ($this->logger)
			{
				$this->logger->fatal($exception);
			}
			
			$_smarty = App::getSmarty();

			if (Config::get("FEATURE_FLAG.BUGSNAG"))
			{
				$this->getBugsnag()->notifyException($exception);
			}

			if ($this->environment == "development")
			{
				$_smarty->assign("exception", $exception);
				$_smarty->display("error_developer.tpl");
			}
			else
			{
				return "error_fatal.tpl";
			}
		}

		/**
		* Browser version check
		* Only check problem browsers like IE, as Chrome/Safari should work fine but throw false positives on the min browser version check.
		* @method browserCheck
		*/
		public function browserCheck()
		{
			/*$minimum_browser_versions = array();
			$useragent = get_browser($_SERVER['HTTP_USER_AGENT'], true);
			$browser_name = $useragent['browser'];
			$browser_version = $useragent['version'];

			if (array_key_exists($browser_name, $minimum_browser_versions)) {
				$min = $minimum_browser_versions[$browser_name];
				if ($browser_version < $min) {
					$report = array_merge($useragent, $_SERVER);
					mail(Config::get('LOGS_ADDRESS'), 'NYKC Login - Unsupported Browser', print_r($report, 1), 'From: nykcentral@nowyouknow.net');
					$_smarty = self::getSmarty();
					print($_smarty->fetch("upgrade_browser.tpl"));
					exit();
				}
			}*/
		}

		/**
		* Runs the browser check and exit out if it fails.
		* @method run
		*/
		public function run()
		{
			//Set the running directory so other files know what the base directory.
			self::$_runningDir = realpath(__DIR__ . "/../"); //realpath() resolves the dots and slashes to make it look more clean.

			//This is still here in spite of Config since there's a lot of legacy stuff that depends on it.
			global $constants;
			
			//Setting the environment in the config allows us to use environment folders in the config folder. Helps a lot when testing out different settings for development vs production and allows settings to be 
			//checked in rather than just sitting solely on the server that uses them.
			$environment = $this->getEnvironment();
			Config::setEnvironment($environment);

			//Get the constants.
			$constants = Config::getAll();

			//Start up a new Logger.
			$this->logger = new Logger([
				"log_dir" => __DIR__ . "/../log/"
				, "report_level" => Config::get("NYK_LOG_REPORT_LEVEL")
				, "line_format" => Config::get("NYK_LOG_DEFAULT_LINE_FORMAT")
				, "time_format" => Config::get("NYK_LOG_DEFAULT_TIME_FORMAT")
			]);
			
			$logger = $this->logger;
			
			$layout = 'layouts/guest.tpl';

			//We want to capture all errors for logging.
			set_error_handler(array($this, "error"), E_ALL);

			//Also catch all fatal errors.
			register_shutdown_function(array($this, "shutdown"));

			ini_set('display_errors', 'Off');
			error_reporting(0);

			//Check the browser to see if they meet the minimum requirements.
			$this->browserCheck();
			
			//Get the Smarty instance.
			$_smarty = App::getSmarty();
			
			try
			{
				//Get the database connection.
				$db = Connection::getConnection(array(
					'phptype' => Config::get("DB_TYPE")
			        , 'hostspec' => Config::get("DB_HOST")
			        , 'port' => Config::get("DB_PORT")
			        , 'database' => Config::get("DB_DATABASE")
			        , 'username' => Config::get("DB_USERNAME")
			        , 'password' => Config::get("DB_PASSWORD")
				));
			}
			catch (Exception $exception)
			{
				$logger->fatal("Could not connect to the database: $exception");
				exit($_smarty->fetch(Config::get("FATAL_ERROR_TEMPLATE")));
			}
		    
			//Get the session.
			$session = new UserSession($db, $logger);
			
			if (!$session)
			{
				$logger->fatal("Could not initialize user session.");
				exit($_smarty->fetch(Config::get("FATAL_ERROR_TEMPLATE")));
			}

			$logger->config["user_id"] = $session->IsLoggedIn() ? $session->getLoggedInCustomer()->CustomerId : "guest";

			if (Config::get("FEATURE_FLAG.BUGSNAG"))
			{
				$user = $session->IsLoggedIn() ? $session->getLoggedInCustomer() : null;

				if ($user)
				{
					$this->getBugsnag()->registerCallback(function ($report) use ($user) {
					    $report->setUser([
					        "id" => $user->CustomerId
					        , "name" => $user->getFullName()
					        , "email" => $user->PrimaryEmailAddress
					    ]);
					});
				}
			}

			//Connect the session to the form.
			Form::session($session);
			
			//Get the model factory.
			$modelFactory = new ModelFactory($db, $logger, $session);

			//Get the action factory.
			$actionFactory = new ActionFactory($db, $session, $logger, $_smarty, $modelFactory, $constants);

			//Start the action.
			$actionRegistry = new ActionRegistry($session, $logger, $_smarty, $actionFactory, $constants);
			
			try
			{
				$defaultAction = $constants['NYKC2_DEFAULT_ACTION'];

				$_smarty->assign("appEnvironment", $this->environment);

				$_smarty->assign("action", "");
				$_smarty->assign("subaction", "");

				$action = $defaultAction;
				if (Input::get("action") !== null)
				{
					$pattern = '/[^a-zA-Z_]+/';

					$purifiedAction = preg_replace($pattern, "", Input::get("action"));
					$action = $purifiedAction;
					
					$_smarty->assign("action", $purifiedAction);

					if (Input::get("subaction") !== null)
					{
						$purifiedAction = preg_replace($pattern, "", Input::get("subaction"));
						$action .= "." . $purifiedAction;

						$_smarty->assign("subaction", $purifiedAction);
					}
				}
				else
				{
					$action = $defaultAction;
				}
				
				if ($session->IsLoggedIn())
				{
					$logger->config["user_id"] = $session->getLoggedInCustomer()->CustomerId;
					$logger->info("User #" . $session->getLoggedInCustomer()->CustomerId . " went to action " . $action . ".");

					$layout = 'layouts/registered.tpl';
				}
				else
				{
					$layout = "layouts/guest.tpl";
				}

				$page = $actionRegistry->processAction($action);
				
				$template = $actionRegistry->displayView($page, $layout);
				
				//Pear DB was throwing errors with this enabled. It should disconnect automatically anyway.
				//$db->disconnect();
			}
			catch (Exception $exception)
			{
				$page = $this->exception($exception);
				$template = $actionRegistry->displayView($page, $layout);
			}

			//Ajax requests don't use a layout.
			$isAjax = $actionRegistry->isAjax($action);

			if ($isAjax)
			{
				print($page);
			}
			else if (!empty($template))
			{
				$display = "extends:$template";

				$_smarty->display($display);
			}
			else
			{
				$display = "extends:" . Config::get("ERROR_TEMPLATE");

				$_smarty->display($display);
			}

			$logger->info("------------------");
		}

		/**
		* Returns the absolute path to the running directory for the app.
		* @method runningDirectory
		*/
		public static function runningDirectory()
		{
			return self::$_runningDir;
		}
	}
<?php

	/**
	* Used to help with authentication and security.
	* @class Auth
	*/

	class Auth
	{
		/**
		* Use this to hash passwords before putting them in the DB.
		*
		* @method hash
		* @param {String} $plaintext
		* @return {String} The encrypted password.
		*/
		public static function hash($plaintext)
		{
			return crypt($plaintext, '$1$');
		}

		/**
		* Encrypts a string.
		*
		* @method encrypt
		* @param {String} string
		* @param {String} key
		* @return {String}
		*/
		public static function encrypt($string, $key) 
		{
			$mod = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
			$max_key_size = mcrypt_enc_get_key_size($mod);
			$key = substr($key, 0, $max_key_size);
			$iv = mcrypt_create_iv(8, MCRYPT_RAND);
			mcrypt_generic_init($mod, $key, $iv);
			$encrypted = mcrypt_generic($mod, $string);
			mcrypt_generic_deinit($mod);
			mcrypt_module_close($mod);

			return $encrypted;
		}
	}
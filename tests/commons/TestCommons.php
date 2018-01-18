<?php
	class TestCommons
	{
	    public static $username = '';
	    public static $password = '';
	    public static $usernameAdmin = '';
	    public static $passwordAdmin = '';

	    public static function logIn($I)
	    {
            $I->amOnPage('/');
            $I->fillField('username', self::$username);
            $I->fillField('password', self::$password);
            $I->click('Login');
	    }

	    public static function logInAdmin($I)
	    {
            $I->amOnPage('/');
            $I->fillField('username', self::$usernameAdmin);
            $I->fillField('password', self::$passwordAdmin);
            $I->click('Login');
	    }

	    public static function logOut($I)
	    {
	    	$I->click("Logout");
	    	$I->see("Login");
	    }

	    public static function errorCheck($I)
	    {
	    	$I->dontSee("Whoops, something went wrong!");
            $I->dontSee("Unrecoverable error");
            $I->dontSee("does not exist");
	    }
	}
<?php

    /**
    * @class Database
    */
	class Database
    {
        public static function getDatabase(Array $config)
        {
            $dsn = array(
                'phptype' => $config["phptype"]
                , 'hostspec' => $config["hostspec"]
                , 'port' => $config["port"]
                , 'database' => $config["database"]
                , 'username' => $config["username"]
                , 'password' => $config["password"]
            );
            $PEARDB = DB::Connect($dsn);
        
            if (DB::isError($PEARDB)) 
            {
                throw new Exception("Failed to connect to the database.");
                return null;
            }
            
            $PEARDB->setFetchMode(DB_FETCHMODE_ASSOC);

            return $PEARDB;
        }
    }
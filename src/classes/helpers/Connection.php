<?php

    /**
    * @class Connection
    */
    class Connection
    {
        public static function getConnection(Array $config) 
        {
            return Database::getDatabase($config);
        }
    }
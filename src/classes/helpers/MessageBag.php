<?php

	/**
    * @class MessageBag
    */
	class MessageBag
    {
        private static $_bags;
        
        public static function add($bag, $message)
        {
        	self::$_bags[$bag][crc32($message)] = $message;
        }

        public static function all($bag)
        {
            if (isset(self::$_bags[$bag]))
            {
                return self::$_bags[$bag];
            }

        	return array();
        }

        public static function dump()
        {
            $bags = [];
            foreach (self::$_bags as $type => $bag)
            {
                $bags[$type] = $bag;
            }

            return $bags;
        }
    }
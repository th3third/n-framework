<?php

    class ConfigTest extends \Codeception\TestCase\Test
    {
        /**
        * @group config
        */
        public function testGetConstants()
        {
            $actual = Config::getConstants();
            $this->assertInternalType("array", $actual);
            $this->assertNotEmpty($actual);
        }

        /**
        * @group config
        */
        public function testGet()
        {
            $actual = Config::get("NYKC2_VERSION");
            $this->assertNotNull($actual);

            $actual = Config::get("FA@#PFPIEHFPI#FP");
            $this->assertNull($actual);
        }

        /**
        * @group config
        */
        public function testGetAll()
        {
            $actual = Config::getAll();
            $this->assertInternalType("array", $actual);
            $this->assertNotEmpty($actual);
        }

        /**
        * @group config
        */
        public function testSetEnvironment()
        {
            $actual = Config::setEnvironment("production");
            $this->assertTrue($actual);

            $this->setExpectedException('UnexpectedValueException');
            Config::setEnvironment("awFAwefawefAWEf32f32323123!@#123");
        }
    }
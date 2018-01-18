<?php

    class AppTest extends \Codeception\TestCase\Test
    {
        protected function getApp()
        {
            return new App();
        }

        public function testGetSmarty()
        {
            $smarty = $this->getApp()->getSmarty();
            $this->assertInstanceOf("Smarty", $smarty);
        }

        protected function testSmartyCacheAccess()
        {

        }

        protected function testStatementsDirAccess()
        {

        }

        protected function testCnsDirAccess()
        {
            
        }
    }
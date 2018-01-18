<?php

    class ActionRegistryTest extends \Codeception\TestCase\Test
    {
        public function testRegisterValid()
        {
            $actual = ActionRegistry::register("BLANK", "blank.tpl");
            $this->assertTrue($actual);
        }

        public function testParseAction()
        {
            $action = "ACTION";
            $subaction = "SUBACTION";
            $actual = ActionRegistry::parseAction("$action.$subaction");
            
            $this->assertEquals(count($actual), 2);
            $this->assertEquals($actual[0], $action);
            $this->assertEquals($actual[1], $subaction);
        }
    }
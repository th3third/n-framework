<?php

    class RedirectTest extends \Codeception\TestCase\Test
    {
        public function testRedirectActionSingle()
        {
            $actual = Redirect::action("ACTION", array("test" => "param"));
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals("ACTION", $actual->action);
            $this->assertNull($actual->subaction);
        }

        public function testRedirectActionMultiple()
        {
            $actual = Redirect::action("ACTION.SUBACTION", array("test" => "param"));
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals("ACTION", $actual->action);
            $this->assertEquals("SUBACTION", $actual->subaction);
        }

        public function testRedirectBackInvalid()
        {
            $actual = Redirect::back();
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals("", $actual->action);
        }

        public function testRedirectBack()
        {
            $_SERVER["HTTP_REFERER"] = "http://localhost/index.php?action=ACCOUNT&subaction=SETTINGS&testvar=testparam";

            $actual = Redirect::back();
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals("ACCOUNT", $actual->action);
            $this->assertEquals("SETTINGS", $actual->subaction);
            $this->assertEquals("testparam", $actual->params["testvar"]);
        }

        public function testRedirectWith()
        {
            $key = "test";
            $message = "Test message.";

            $actual = Redirect::action("TEST")->with($key, $message);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($message, $actual->flash[$key][0]);
        }

        public function testRedirectWithArray()
        {
            $key = "test";
            $messages = array(
                "Test message 1."
                , "Test message 2."
                , "Test message 3."
            );

            $actual = Redirect::action("TEST")->with($key, $messages);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($messages, array_intersect($messages, $actual->flash[$key]));
        }

        public function testRedirectWithSuccess()
        {
            $message = "Test message.";

            $actual = Redirect::action("TEST")->withSuccess($message);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($message, $actual->flash["success"][0]);
        }

        public function testRedirectWithSuccessArray()
        {
            $messages = array(
                "Test message 1."
                , "Test message 2."
                , "Test message 3."
            );

            $actual = Redirect::action("TEST")->withSuccess($messages);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($messages, array_intersect($messages, $actual->flash["success"]));
        }

        public function testRedirectWithError()
        {
            $message = "Test message.";

            $actual = Redirect::action("TEST")->withError($message);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($message, $actual->flash["error"][0]);
        }

        public function testRedirectWithErrorArray()
        {
            $messages = array(
                "Test message 1."
                , "Test message 2."
                , "Test message 3."
            );

            $actual = Redirect::action("TEST")->withError($messages);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($messages, array_intersect($messages, $actual->flash["error"]));
        }

        public function testRedirectWithInfo()
        {
            $message = "Test message.";

            $actual = Redirect::action("TEST")->withInfo($message);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($message, $actual->flash["info"][0]);
        }

        public function testRedirectWithInfoArray()
        {
            $messages = array(
                "Test message 1."
                , "Test message 2."
                , "Test message 3."
            );

            $actual = Redirect::action("TEST")->withInfo($messages);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($messages, array_intersect($messages, $actual->flash["info"]));
        }

        public function testRedirectWithDebug()
        {
            $message = "Test message.";

            $actual = Redirect::action("TEST")->withDebug($message);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($message, $actual->flash["debug"][0]);
        }

        public function testRedirectWithDebugArray()
        {
            $messages = array(
                "Test message 1."
                , "Test message 2."
                , "Test message 3."
            );

            $actual = Redirect::action("TEST")->withDebug($messages);
            $this->assertInstanceOf("Redirect", $actual);
            $this->assertEquals($messages, array_intersect($messages, $actual->flash["debug"]));
        }

        //TODO
        /*public function testRedirectWithInputErrors()
        {
            $input = array(

            );
        }*/
    }
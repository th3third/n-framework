<?php

    class InputTest extends \Codeception\TestCase\Test
    {
        public function testInputGet()
        {
            $expected = $_REQUEST["testparam"] = "testvalue"; 

            $actual = Input::get("testparam");

            $this->assertEquals($expected, $actual);
        }

        public function testInputPost()
        {
            $expected = $_POST["testparam"] = "testvalue"; 

            $actual = Input::post("testparam");

            $this->assertEquals($expected, $actual);
        }

        public function testInputAny()
        {
            $expected = $_REQUEST["testparam"] = "testvalue"; 

            $actual = Input::any("testparam");

            $this->assertEquals($expected, $actual);
        }

        public function testInputAll()
        {
            $_REQUEST["testparam1"] = "testvalue1";
            $_REQUEST["testparam2"] = "testvalue2";
            $expected = array(
                "testparam1" => "testvalue1"
                , "testparam2" => "testvalue2"
            );

            $actual = Input::all();

            $this->assertEquals($expected, array_intersect($expected, $actual));
        }

        public function testInputFile()
        {
            $expected = $_FILES["testfile"] = array(
                "name" => "Testfile.txt"
                , "type" => "text/plain"
                , "tmp_name" => "/tmp/php/tmp123"
                , "error" => UPLOAD_ERR_OK
                , "size" => 123
            );
            $actual = Input::file("testfile");

            $this->assertEquals($expected, array_intersect($expected, $actual->file));
        }

        public function testInputFileInsertion()
        {
            $expected = $_FILES["testfile"] = array(
                "name" => "Testfile.txt"
                , "type" => "text/plain"
                , "tmp_name" => "/tmp/php/tmp123"
                , "error" => UPLOAD_ERR_OK
                , "size" => 123
            );
            $actual = Input::file($expected);

            $this->assertEquals($expected, array_intersect($expected, $actual->file));
        }

        //TODO: test Input::files()

        public function testInputFileInvalid()
        {
            $actual = Input::file("testfile");

            $this->assertNull($actual);
        }
    }
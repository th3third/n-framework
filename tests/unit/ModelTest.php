<?php

    class ModelTest extends NYKCTest
    {
        protected $model;

        public function model()
        {
            $logger = new Logger(array(
                "log_dir" => __DIR__ . "/../log/"
            ));
            $db = Connection::getConnection(array(
                'phptype' => Config::get("DB_TYPE")
                , 'hostspec' => Config::get("DB_HOST")
                , 'port' => Config::get("DB_PORT")
                , 'database' => "nykctest2"
                , 'username' => Config::get("DB_USERNAME")
                , 'password' => Config::get("DB_PASSWORD")
            ));

            return new Model($db, $logger);
        }


        public function testMem()
        {
            $start = memory_get_usage(true);

            $model = $this->factory->build("Customer")->all();

            $mid = memory_get_usage(true) - $start;
            fwrite(STDERR, print_r("\r\n Used to instantiate: " . $this->formatSizeUnits($mid), TRUE));

            $model = null;

            $after = memory_get_usage(true) - $start;
            fwrite(STDERR, print_r("\r\n After freeing: " . $this->formatSizeUnits($after), TRUE));
            fwrite(STDERR, print_r("\r\n", TRUE));

            //$this->assertEquals($this->formatSizeUnits($start), $this->formatSizeUnits($after));
        }

        /*public function testCanCreate()
        {
            $model = $this->factory->build("Model")->create();

            //Default create should be true.
            $this->assertTrue($model->canCreate());
        }

        public function testCanRead()
        {
            $model = $this->factory->build("Model")->create();

            //Default read should be true.
            $this->assertTrue($model->canRead());
        }

        public function testCanUpdate()
        {
            $model = $this->factory->build("Model")->create();

            //Default update should be true.
            $this->assertTrue($model->canUpdate());
        }

        public function testCanDelete()
        {
            $model = $this->factory->build("Model")->create();

            //Default delete should be true.
            $this->assertTrue($model->canDelete());
        }*/
    }
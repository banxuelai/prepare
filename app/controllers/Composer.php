<?php
use Monolog\Logger;
class Composer
{
    public function monolog()
    {
        $logger = new Logger('my_logger');
        print_r($logger);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: banxuelai
 * Date: 2019/1/8
 * Time: 20:14
 */
#require 'test1.php';
#require 'test2.php';

/*Test1\test();
Test2\test();*/

spl_autoload_register();

test2::ttt();


/*function __autoload($class)
{
    require __DIR__.'\\'.$class.'.php';
}*/
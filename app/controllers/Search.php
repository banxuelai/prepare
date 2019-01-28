<?php
/**
 * Created by PhpStorm.
 * User: banxuelai
 * Date: 2018/12/19
 * Time: 14:52
 */


defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends CI_Controller {

    public $id = 0;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('search_model');
        $this->id = 1;
    }

    /**
     * @desc search
     * @author banxuelai@vcomic.com
     * @date 2018/12/19
     */
    public function search()
    {
       // $this->search_model->test();

        # test
        $arr = array(
            'id'    => 1,
            'name'  => 'banxuelai',
        );

        $this->displayJson($arr);
    }

    public function test()
    {
        $sc = "{2RQROH3E-4EW0-28RQ-6ZOR-UE6A2F5357I2}";
   $version = '1.1';
  $type = 'ios';
   $channel = 'normal';
   $uri = "/user/check_user_white";
   echo md5("{$uri}{$version}{$type}{$channel}{$sc}").PHP_EOL;
  echo "{$uri}{$version}{$type}{$channel}{$sc}";

    }

    public function serc()
    {
        $str = "4hx1qTY/B10KL5j2TZP01dIRnqVw7k4LJyIMYNOd0xKfw4+dNqjdt4sbnf7P6IRS";
        $key = '{2RQROH3E-4EW0-28RQ-6ZOR-UE6A2F5357I2}';
        $this->load->library('Aes');
        $re = $this->aes->opensslDecrypt($str, $key);
        //var_dump($re);
        exit($re);
    }

    public function compare()
    {
        $str1 = "/user/check_user_white1.1iosnormal{2RQROH3E-4EW0-28RQ-6ZOR-UE6A2F5357I2}";
        $str2 = "/user/check_user_white1.1iosnormal{2RQROH3E-4EW0-28RQ-6ZOR-UE6A2F5357I2}";

        var_dump( $str1 === $str2);
    }
}
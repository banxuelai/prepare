<?php

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public  function test()
    {
        print_r('111');exit;
        $this->load->library('Aes.php');
        $this->load->model('user_model');
    }

    public function show()
    {
        print_r('1111111111');
    }
}
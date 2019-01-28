<?php

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public  function test()
    {
        $this->load->library('Aes.php');
        $this->load->model('user_model');
    }
}
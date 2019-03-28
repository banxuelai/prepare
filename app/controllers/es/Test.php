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

    public function syn_es_data()
    {
        $this->load->model('search_model');
        $this->search_model->synDataToEs();
    }
}
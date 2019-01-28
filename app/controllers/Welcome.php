<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function test()
    {
        echo phpinfo();exit;
        print_r(getMca());exit;
        $str = "1f2dd1c42cd2d9c36db27c60239c86cb";
        $num =  base_convert($str,36,10);
        echo $num.PHP_EOL;
        $num = substr($num,8);
        echo $num;
        exit;
        $arr = range(1,10000);

        foreach ($arr as $value)
        {
            echo $value.':'.base_convert($value,10,32).PHP_EOL;
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: banxuelai
 * Date: 2018/12/19
 * Time: 14:52
 */


defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('search_model');
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

}
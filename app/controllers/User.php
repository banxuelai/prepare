<?php
/**
 * Created by PhpStorm.
 * User: banxuelai
 * Date: 2018/12/19
 * Time: 14:52
 */


defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    /**
     * @desc check_user_white
     * @author banxuelai@vcomic.com
     * @date 2018/12/19
     */
    public function check_user_white()
    {
        $userId = 1;

        $r = $this->user_model->getUserWhiteRowByUserId($userId);

        $this->displayJson(array('code'      => 1, 'message'   => 'ok','data' => $r));
    }

}
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class User_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc getUserWhiteRowByUserId
     * @param $userId
     * @return mixed
     * @author banxuelai@vcomic.com
     * @date 2018/12/19
     */
    public function getUserWhiteRowByUserId($userId)
    {
        return $this->service('user/user_whitelist_service')->getUserWhiteRowByUserId($userId);
    }
}
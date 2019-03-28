<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Search_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function synDataToEs()
    {
        return $this->service('es/es_prepare_service')->synDataToEs();
    }
}
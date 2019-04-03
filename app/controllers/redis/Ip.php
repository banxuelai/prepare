<?php

class Ip extends  CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc 导入IP地址库
     * @author banxuelai@vcomic.com
     * @date 2019/4/3
     */
    public function import_ips_to_redis()
    {
        $fileName = "/GeoLite2-City-Blocks-IPv4.csv";

        $this->load->model('ip_model');

        $r = $this->ip_model->importIpsToRedis($fileName);

        $this->displayJson($r);
    }

    /**
     * @desc 导入城市信息
     * @author banxuelai@vcomic.com
     * @date 2019/4/3
     */
    public function import_cities_to_redis()
    {
        $fileName = "/GeoLite2-City-Locations-zh-CN.csv";

        $this->load->model('ip_model');

        $r = $this->ip_model->importCitiesToRedis($fileName);

        $this->displayJson($r);
    }
}
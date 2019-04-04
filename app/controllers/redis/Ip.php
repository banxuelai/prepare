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
        $fileName = "D:\WAMP\GeoLite2-City-Blocks-IPv4.csv";

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
        $fileName = "D:\WAMP\GeoLite2-City-Locations-zh-CN.csv";

        $this->load->model('ip_model');

        $r = $this->ip_model->importCitiesToRedis($fileName);

        $this->displayJson($r);
    }

    /**
     * @desc 查询IP所属城市
     * @author banxuelai@vcomic.com
     * @date 2019/4/4
     */
    public function get_city_by_ip()
    {
        $ip = trim(g('ip'));

        $this->load->model('ip_model');

        $r = $this->ip_model->getCityByIp($ip);

        $this->displayJson(array('code' => 1, 'message' => 'ok', 'data' => $r));
    }
}
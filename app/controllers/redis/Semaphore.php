<?php

class Semaphore extends  CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc 获取信号量
     * @author banxuelai@vcomic.com
     * @date 2019/4/23
     */
    public function get_semaphore()
    {
        #$limit = intval(p('limit'));

        $this->load->model('semaphore_model');

        $r = $this->semaphore_model->getSemaphore($key = "Semaphore_20190423", $limit = 10, $timeOut = 10);

        $this->displayJson(array('code' => 1, 'message' => 'ok', 'data' => $r));
    }
}

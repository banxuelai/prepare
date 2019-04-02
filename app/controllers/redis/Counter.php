<?php

class Counter extends  CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc 更新计数器
     * @author banxuelai@vcomic.com
     * @date 2019/4/2
     */
    public function update_counter()
    {
        $name = 'art';
        $count = 11;

        $this->load->model('counter_model');
        $r = $this->counter_model->UpdateCounter($name, $count);

        $this->displayJson($r);
    }

    public function get_counter()
    {
        $name = 'art';
        $prec = 1;

        $this->load->model('counter_model');
        $r = $this->counter_model->getCounter($name, $prec);

        $this->displayJson(array('code' => 1, 'message' => 'ok', 'data' => $r));
    }
}
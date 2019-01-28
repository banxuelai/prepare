<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public $loginUserId = 0;
    public $appVersion = '';    # 客户端版本号
    public $appType = '';       # 客户端来源 android ios 两种
    public $appChannel  = '';   # 渠道
    public $appMark = '';       # 硬件唯一标识
    public $random = '';        # 时间戳

    public $debug = '';

    public function __construct()
    {
        parent::__construct();

        $this->appVersion = g('_version');
        $this->appType = g('_type');
        $this->appChannel = g('_channel');
        $this->appMark = g('_mark');
        $this->random = isset($_SERVER['HTTP_RANDOM']) ? $_SERVER['HTTP_RANDOM'] : '';
        $this->sign = isset($_SERVER['HTTP_SIGN']) ? $_SERVER['HTTP_SIGN'] : '';

        $this->debug = 'yes';

        # 验证来源
        if(! in_array($this->appType, array('android','ios'))) {
            $this->displayJson(array('code' => 0, 'message' => 'sign_error_t'));
        }

        # 有效期验证
       if(! $this->checkRandom($this->random, 100)) {
            $this->displayJson(array('code' => 0, 'message' => 'sign_error_r'));
        }

       // $sign = g('sign');

        #  {$_SERVER['REQUEST_URI']}
        $parseUrl = parse_url($_SERVER['REQUEST_URI']);
        $path = $parseUrl['path'];

        $secretKey = $this->getSecrectKey();

        $newSign = md5("{$path}{$this->appVersion}{$this->appType}{$this->appChannel}{$secretKey}");

        if(! ($this->sign === $newSign)) {
            $this->displayJson(array('code' => 0, 'message' => 'sign_error_s'));
        }

        $this->loginUserId();
    }

    // header加密密钥
    protected function getSecrectKey()
    {
        return '{2RQROH3E-4EW0-28RQ-6ZOR-UE6A2F5357I2}';
    }

    // result加密密钥
    protected function getAesResultJsonSecretKey()
    {
        return '{2RQROH3E-4EW0-28RQ-6ZOR-UE6A2F5357I2}';
    }

    // 校验有效期
    protected function checkRandom($random, $timeOut = 5)
    {
        $random = intval($random);
        $timeOut = intval($timeOut);

        if((time() - $random) > $timeOut) {
            return FALSE;
        }

        return TRUE;
    }

    public function loginUserId()
    {
        return $this->loginUserId;
    }

    /**
     * 抛出 Json（Aes加密）
     * @param $data
     */
    public function displayJson($data)
    {
        $mca = getMca();
        $output = '';
        $this->load->library('Aes');
        $output .= $this->aes->opensslEncrypt(json_encode($data), $this->getAesResultJsonSecretKey());
        exit($output);
        if($this->debug == 'yes' || in_array($mca['mca'], $this->noEncryptedMcaCfg))
        {
            header("Content-type: application/json; charset=utf-8", TRUE);
            $output .= json_encode($data);
        }
        else {
            mhMark('display_aes_start');
            $this->load->library('Aes');
            $output .= $this->aes->encrypt(json_encode($data), $this->getAesResultJsonSecretKey());
            mhElapsedTime('display_aes_start', 'display_aes_end');
        }

        //$this->beforeDisplay($data);

        exit($output);
    }
}

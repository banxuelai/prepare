<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Counter_model extends CI_Model
{
    # 以秒位为单位的计数器精度 1s 5s 1min 5min 1h 5h 1day
    private $precisionArr = array(1, 5, 60, 300, 3600, 18000, 86400);

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc 更新计数器
     * @param $name
     * @param $count
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/4/2
     */
    public function  UpdateCounter($name, $count)
    {
        $name   = trim($name);
        $count  = intval($count);

        # 参数验证
        if(empty($name) || empty($count)) {
            return array('code' => 0, 'message' => "参数异常");
        }

        $nowTime = time();

        foreach($this->precisionArr as $prec)
        {
            # 获取当前时间片的开始时间
            $pnow = ($nowTime / $prec) * $prec;
            $key = $prec.':'.$name;

            # 写入有序集合
            $zkey = "test_know";
            $this->redisInit()->master()->zadd($zkey, $key, 0);

            # 写入散列
            $hkey = 'test_count'.$key;
            $this->redisInit()->master()->hincrby($hkey, $pnow, $count);
        }
        return array('code' => 1, 'message' => 'ok');
    }

    /**
     * @desc 获取计数器
     * @param $name
     * @param $prec
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/4/2
     */
    public function getCounter($name, $prec)
    {
        $name = trim($name);
        $prec = intval($prec);

        $returnData = array(
            'data' => array(),
        );

        # 参数验证
        if(empty($name) || empty($prec) || ! in_array($prec, $this->precisionArr)) {
            return $returnData;
        }

        $key = $prec.':'.$name;
        $hkey = 'test_count'.$key;

        # 散列表数据
        $data = $this->redisInit()->slave()->hgetall($hkey);
        krsort($data);

        return array(
            'data' => $data,
        );
    }
}
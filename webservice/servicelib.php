<?php
class Servicelib extends CI_Model
{
    static public $redisConn = null;

    public function __construct()
    {
        parent::__construct();
    }

    function redisInit()
    {
        if(self::$redisConn === null) {
            $this->load->library('redis');
            self::$redisConn = $this->redis;
        }
        return self::$redisConn;
    }


    /**
     * KeyName
     * @param $nameArr
     * @param $separator
     * @return string
     */
    function getSpliceStr($nameArr, $separator)
    {
        $spliceStr = '';
        if(empty($nameArr)) {
            return $spliceStr;
        }

        $separator = trim($separator);
        foreach($nameArr as $key => $val)
		{
			$spliceStr .= $spliceStr ? $separator . $key . $separator . $val : $key . $separator . $val;
		}

        return $spliceStr;
    }

}

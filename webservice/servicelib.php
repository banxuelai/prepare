<?php
class Servicelib extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    # 用户白名单
    const Table_User_Whitelist = 'user_whitelist';

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

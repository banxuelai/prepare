<?php
class Servicelib extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    # 用户白名单
    const Table_User_Whitelist = 'user_whitelist';

    # comic相关
    const Table_Comic_Info = 'comic_info';
    const Table_Comic_Author_Map = 'comic_author_map';
    const Table_Comic_Author_Info = 'comic_author_info';

    # 项目信息表
    const Table_Project_Info = 'project_info';

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

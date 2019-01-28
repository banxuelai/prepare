<?php
class user_whitelist_service extends servicelib
{
    /**
     * @desc getUserWhiteRowByUserId
     * @param $userId
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2018/12/19
     */
    public function getUserWhiteRowByUserId($userId)
    {
        $userId = intval($userId);

        if(empty($userId)) {
            return array();
        }


        return $this->master()->select('user_id, white_status')->from(self::Table_User_Whitelist)
            ->where('user_id', $userId)->limit(1)->get()->row_array();
    }
}
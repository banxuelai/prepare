<?php

/**
 * Class NewYear
 * Hooks 测试--拜年
 */
class NewYear
{
    /**
     * @desc call_happy_new_year
     * @param $params
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/1/27
     */
    public function call_happy_new_year($params)
    {
        $params = is_array($params) ? $params : array();

        # pre_name
        $preName = empty($params[0]) ? '' : trim($params[0]);

        # post_name
        $postName = empty($params[1]) ? '' : trim($params[1]);

        $happy = "{$preName} say happy new year to {$postName}";

        print_r($happy);
        //return $params;
    }
}

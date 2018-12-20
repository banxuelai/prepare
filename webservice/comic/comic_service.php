<?php
/**
 * Created by PhpStorm.
 * User: banxuelai
 * Date: 2018/12/20
 * Time: 15:24
 */

class Comic_comic_service extends   Servicelib
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc getComicRowByComicId
     * @param $comicId
     * @param $fields
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function getComicRowByComicId($comicId, $fields)
    {
        $comicId = intval($comicId);
        $fields = trim($fields);

        if(empty($comicId) || empty($fields)) {
            return array();
        }

        return $this->master()->select($fields)->from(self::Table_Comic_Info)
            ->where('comic_id', $comicId)->limit(1)->get()->row_array();
    }

    /**
     * @desc getWbComicAuthorListByComicId
     * @param $comicId
     * @param $fields
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function getWbComicAuthorListByComicId($comicId, $fields)
    {
        $comicId = intval($comicId);
        $fields = trim($fields);

        if(empty($comicId) || empty($fields)) {
            return array();
        }

        # 获取comic_author_map
        $authorMapList = $this->master()->select('author_id')->from(self::Table_Comic_Author_Map)
            ->where('comic_id', $comicId)->get()->result_array('author_id');

        if(empty($authorMapList)) {
            return array();
        }

        $authorIdArr = array_column($authorMapList, 'author_id');

        # 用户信息列表
        return $this->master()->select($fields)->from(self::Table_Comic_Author_Info)
            ->where_in('author_id', $authorIdArr)->get()->result_array('author_id');
    }

}
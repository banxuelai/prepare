<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Article_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc 发布文章
     * @param $userId
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/3/27
     */
    public function postArticle($userId)
    {
        $userId = intval($userId);

        if(empty($userId)) {
            return array('code' => 0, 'message' => '参数异常');
        }
/*
        $key = "queue";

        # 开启事务
        $this->redisInit()->master()->multi();*/

        # 生成article_id
        $articleId = $this->redisInit()->master()->incr('article:');

        # 文章已投票用户名单
        $voted = "voted:".$articleId;
        $user = "user :".$userId;
        $this->redisInit()->master()->sadd($voted,$user);

        $nowTime = time();
        $article = "article:".$articleId;

        # 时间排序有序集合
        $this->redisInit()->master()->zadd('time:',$nowTime,$article);
        # 评分排序有序集合
        $this->redisInit()->master()->zadd('score:',1,$article);

        return array('code' => 1, 'message' => 'ok');

    }
}
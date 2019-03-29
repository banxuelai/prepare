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

        # 开启事务
        //$this->redisInit()->master()->multi();

        # 生成article_id
        $articleId = $this->redisInit()->master()->incr('art_article');

        # 文章已投票用户名单
        $votedKey = "art_voted:".$articleId;
        $user = "user :".$userId;

        $this->redisInit()->master()->sadd($votedKey,$user);

        $nowTime = time();
        $article = "article:".$articleId;

        # 时间排序有序集合
        $this->redisInit()->master()->zadd('art_time',$nowTime,$article);
        # 评分排序有序集合
        $this->redisInit()->master()->zadd('art_score',1,$article);

       // $this->redisInit()->master()->exec();

        return array('code' => 1, 'message' => 'ok');

    }

    /**
     * @desc 用户投票
     * @param $userId
     * @param $articleId
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/3/28
     */
    public function articleVote($userId, $articleId)
    {
        $userId     = intval($userId);
        $articleId  = intval($articleId);

        # 参数验证
        if(empty($userId) || empty($articleId)) {
            return array('code' => 0, 'message' => "参数异常");
        }

        # 验证文章投票有效期
        $artTimeKey = "art_time";
        $article = "article:".$articleId;

        $time  = $this->redisInit()->slave()->zscore($artTimeKey, $article);
        if(time() - $time > 7 * 86400)
        {
            return array('code' => 0, 'message' => "该文章已过投票期");
        }

        # 验证用户是否投过
        $votedKey = "art_voted:".$articleId;
        $user = "user :".$userId;

        if($this->redisInit()->master()->sadd($votedKey, $user)) {
            $this->redisInit()->master()->zincrby('art_score', 1, $article);
        }
        else{
            return array('code' => 0, 'message' => "您已经投过票了");
        }

        return array('code' => 1, 'message' => 'ok');
    }

    /**
     * @desc 文章列表
     * @param $pageNum
     * @param $rowsNum
     * @param $type
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/3/28
     */
    public function getArticleListByPage($pageNum, $rowsNum, $type)
    {
        $pageNum      = intval($pageNum);
        $pageNum      = max(abs($pageNum), 1);
        $rowsNum      = intval($rowsNum);
        $rowsNum      = max(abs($rowsNum), 1);

        $returnData = array(
            'page_num'          	=> $pageNum,
            'rows_num'          	=> $rowsNum,
            'rows_total'        	=> 0,
            'page_total'        	=> 0,
            'data'              	=> array(),
        );

        $key = "art_".$type;
        $isKey = $this->redisInit()->slave()->exists($key);

        if(! $isKey) {
            return $returnData;
        }

        $rowsTotal = $this->redis->slave()->zCard($key);

        $offset = ($pageNum - 1) * $rowsNum;
        $limit  = ($pageNum * $rowsNum) - 1;
        $articleList = $this->redisInit()->slave()->zRevRange($key, $offset, $limit);

        $articleFormatList = array();
        if(! empty($articleList))
        {
            foreach ($articleList as $row)
            {
                $articleFormatList[] = array(
                    'art_id'    => explode(":", $row)[1],
                    'type'      => 'article',
                );
            }
        }

        return  array(
            'page_num'          	=> $pageNum,
            'rows_num'          	=> $rowsNum,
            'rows_total'        	=> $rowsTotal,
            'page_total'        	=> 0,
            'data'              	=> $articleFormatList,
        );
    }
}
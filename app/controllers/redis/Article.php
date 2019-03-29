<?php

class Article extends  CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc 发布文章
     * @author banxuelai@vcomic.com
     * @date 2019/3/27
     */
    public function post_article()
    {
        $userId = intval(p('user_id'));

        $this->load->model('article_model');
        $r = $this->article_model->postArticle($userId);

        $this->displayJson($r);
    }

    /**
     * @desc 用户投票
     * @author banxuelai@vcomic.com
     * @date 2019/3/27
     */
    public function article_vote()
    {
        $userId = intval(p('user_id'));
        $articleId = intval(p('article_id'));

        $this->load->model('article_model');
        $r = $this->article_model->articleVote($userId, $articleId);

        $this->displayJson($r);
    }

    /**
     * @desc 文章列表
     * @author banxuelai@vcomic.com
     * @date 2019/3/28
     */
    public function article_list()
    {
        $type = trim(g('type'));
        $pageNum = max(intval(g('page_num')), 1);
        $rowsNum = intval(g('rows_num'));

        $this->load->model('article_model');
        $r = $this->article_model->getArticleListByPage($pageNum, $rowsNum, $type);

        $this->displayJson(array('code' => 1, 'message' => 'ok', 'data' => $r));
    }
}
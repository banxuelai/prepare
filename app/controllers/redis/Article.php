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
     * @desc 文章投票
     * @author banxuelai@vcomic.com
     * @date 2019/3/27
     */
    public function article_vote()
    {

    }
}
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Semaphore_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc 获取信号量
     * @param $key
     * @param $limit
     * @param int $timeOut
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/4/23
     */
    public function getSemaphore($key, $limit, $timeOut = 10)
    {
        $limit = intval($limit);
        if(empty($limit) || empty($key)) {
            return '';
        }

        $nowTime = time();
        # 生成唯一标识
        $id = md5(uniqid());
        # 添加前先移除过期
        $this->redisInit()->master()->zremrangebyscore($key, 0, $nowTime - $timeOut);

        # 添加到集合
        $this->redisInit()->master()->zadd($key, $nowTime, $id);

        # 尝试获取信号量
        # 检测自己在有序集合的排名
        $rank = $this->redisInit()->zrank($key, $id);

        if($rank < $limit) {
            return $id;
        }

        # 获取失败 删除之前添加的标识符
        $this->redisInit()->master()->zrem($key, $id);

        return '';
    }
}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class CI_Redis
{
    static private $arrInstance = array();
    private $redis = null;

    public function __construct() {}

    /**
     * 初始化主链接
     * @param $server
     * @return Redis
     */
    private function masterInit($server)
    {
        $host = @constant(strtoupper("{$server}_REDIS_ADD"));
        $port = @constant(strtoupper("{$server}_REDIS_PORT"));

        if($host && $port) {
            $redis = new Redis();
            $redis->connect($host, $port);
            # redis认证密码当前仅限于测试163
            $redis->auth(REDIS_AUTH_PASS);
            return $redis;
        } else {
            // todo;
        }
        return null;
    }

    /**
     * 初始化从连接
     * @param $server
     * @return Redis
     */
    private function slaveInit($server)
    {
        $host = @constant(strtoupper("{$server}_REDIS_ADD_SLAVE"));
        $port = @constant(strtoupper("{$server}_REDIS_PORT"));

        if($host && $port) {
            $redis = new Redis();
            $redis->connect($host, $port);
            # redis认证密码当前仅限于测试163
            if(ENVIRONMENT == 'testing') {
            	$redis->auth(REDIS_AUTH_PASS);
            }
            return $redis;
        } else {
            // todo;
        }
        return null;
    }

    /**
     * 返回服务器default的主链接
     * @param string $server
     * @return mixed
     */
    public function master($server='default')
    {
        if(! isset(self::$arrInstance['master'][$server])) {
            self::$arrInstance['master'][$server] = $this->masterInit($server);
        }
        $this->redis = self::$arrInstance['master'][$server];

        return $this;
    }

    /**
     * 返回服务器default的从链接
     * @param string $server
     * @return mixed
     */
    public function slave($server='default')
    {
        if(! isset(self::$arrInstance['slave'][$server])) {
            self::$arrInstance['slave'][$server] = $this->slaveInit($server);
        }
        $this->redis = self::$arrInstance['slave'][$server];

        return $this;
    }

    public function __call($name, $args)
    {
        $s = getMillisecond();
        $r = call_user_func_array(array(&$this->redis, $name), $args);

        # redis统计信息(非命令行下)
        if(! (php_sapi_name() === 'cli' OR defined('STDIN')))
        {
            $GLOBALS['tj_redis_info'][] = array(
                'redis_info' => $args,
                'redis_time' => getMillisecond() - $s
            );
        }

        return $r;
    }

    /**
     * 构造真实的 redis key
     * @param $key
     * @param $params
     * @return string
     */
    public function getRealKey($key, $params=array())
    {
        $s = vsprintf($key, $params);
        if(defined('ENVIRONMENT')) {
            $s = ENVIRONMENT . ':manhua_1:' . $s;
        }
        return $s;
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * 模型类
 */

class CI_Model {

    static public $serviceInstance = array();
    static public $dbConn = array();


    // 构造函数
	public function __construct()
    {

    }

    /*
     * __get()  这个NB  $this->load 不存在可以用 get_instance()的
     * Model类的代码也非常少，有用的方法就下面这一个，
     * 下面这个方法是为了在Model里面可以像控制器那么通过$this->做很多事情。
     * 例如想在model里面加载某个library，就可以$this->load->library(xxx)，
     * 其实它都是盗用controller的。
     */
	public function __get($key)
	{
		return get_instance()->$key;
	}

    public function master($dbName = 'default')
    {
        if(! isset(self::$dbConn[$dbName]['master']) || empty(self::$dbConn[$dbName]['master'])) {
            self::$dbConn[$dbName]['master'] = $this->load->database("{$dbName}_master", TRUE);
        }
        return self::$dbConn[$dbName]['master'];
    }

    public function slave($dbName = 'default')
    {
        if(! isset(self::$dbConn[$dbName]['slave']) || empty(self::$dbConn[$dbName]['slave'])) {
            $dbSlaveCount = rand(1, constant(strtoupper("DB_{$dbName}_SLAVE_COUNT")));

            self::$dbConn[$dbName]['slave'] = $this->load->database("{$dbName}_slave{$dbSlaveCount}", TRUE);
        }

        return self::$dbConn[$dbName]['slave'];
    }

    /**
     * @desc service
     * @param $path
     * @return mixed
     * @date 2018/12/19
     */
    public function service($path)
    {
        // $path = wbcomic/user/user_service
        if(empty(self::$serviceInstance[$path]))
        {
            @list($module, $class) = explode('/', $path);
            if(empty($module) || empty($class)) {
                exit("Service {$path} Err!");
            }

            // servicelib webservice/servicelib.php  ---> Servicelib
            $libClass = "Servicelib";
            if(! class_exists($libClass))
            {
                $fname = "webservice/servicelib.php";

                $fpath = FCPATH . $fname;
                if(! file_exists($fpath)) {
                    exit("ServiceLib {$fname} Not Found!");
                }
                require_once ($fpath);
            }

            // module servicelib webservice/wbcomic/user_servicelib.php ---> Wbcomic_user_servicelib
/*            $moduleClass = ucfirst("{$module}_servicelib");

            if(! class_exists($moduleClass))
            {
                $fname = "webservice/{$module}_servicelib.php";
                $fpath = FCPATH . $fname;

                if(! file_exists($fpath)) {
                    exit("ServiceLib {$fname} Not Found!");
                }
                require_once ($fpath);
            }*/

            // webservice/wbcomic/user/user_service.php ---> Wbcomic_user_service
            $className = ucfirst("{$class}");
            if(! class_exists($className))
            {
                $fname = "webservice/{$path}.php";
                $fpath = FCPATH . $fname;

                if(! file_exists($fpath)) {
                    exit("Service {$fname} Not Found!");
                }
                require_once ($fpath);
            }

            self::$serviceInstance[$class] = new $className();
        }

        return self::$serviceInstance[$class];
    }

}

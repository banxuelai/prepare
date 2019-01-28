<?php


defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 控制器类
 */
class CI_Controller {

	private static $instance;

    // 构造函数
	public function __construct()
	{
	    // 通过self::$instance实现单例
        // 以后可以通过&get_instance()来获取这个单例
		self::$instance =& $this;

        // 把所有加载的组件都给超级控制器
		foreach (is_loaded() as $var => $class)
		{
			$this->$var =& load_class($class);
		}

		// 初始化Loader组件
		$this->load =& load_class('Loader', 'core');
		$this->load->initialize();
		log_message('info', 'Controller Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * 创建一个用来实例化对象的方法
	 */
	public static function &get_instance()
	{
		return self::$instance;
	}

    /**
     * 抛出 Json
     * @param $data
     */
    public function displayJson($data)
    {

        header("Content-type: application/json; charset=utf-8", true);
        exit(json_encode($data));
    }


}

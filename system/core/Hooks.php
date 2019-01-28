<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class CI_Hooks 钩子类
 * 主要作用是CI框架下扩展base_system 在CI启动时运行一些开发者定义的一些方法来实现一些特定的功能
 * 在不修改系统核心文件的基础上来改变或者增加系统的核心运行功能
 */
class CI_Hooks {


    // 检测hook是否开启
	public $enabled = FALSE;


	// config/hooks.php中的hooks配置信息
	public $hooks =	array();


	// 数组与类对象使用钩子方法
	protected $_objects = array();


	// 防止死循环 因为钩子程序里面可能还有钩子
	protected $_in_progress = FALSE;


	/*
	 * 构造函数
	 */
	public function __construct()
	{
	    // 初始化 获取 hooks配置
		$CFG =& load_class('Config', 'core');
		log_message('info', 'Hooks Class Initialized');

		// 检测配置是否开启钩子
        // 如果配置文件中设置了不允许hooks 则直接返回退出本函数
		if ($CFG->item('enable_hooks') === FALSE)
		{
			return;
		}

		if (file_exists(APPPATH.'config/hooks.php'))
		{
			include(APPPATH.'config/hooks.php');
		}

		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/hooks.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/hooks.php');
		}

		if ( ! isset($hook) OR ! is_array($hook))
		{
			return;
		}

		// 把钩子信息都保存到Hook组件中
		$this->hooks =& $hook;
		$this->enabled = TRUE;
	}

	// --------------------------------------------------------------------

	/*
	 * 外部其实就是调用call_hook函数进行调用钩子程序
	 */
	public function call_hook($which = '')
	{
		if ( ! $this->enabled OR ! isset($this->hooks[$which]))
		{
			return FALSE;
		}

		if (is_array($this->hooks[$which]) && ! isset($this->hooks[$which]['function']))
		{
			foreach ($this->hooks[$which] as $val)
			{
				$this->_run_hook($val);
			}
		}
		else
		{
			$this->_run_hook($this->hooks[$which]);
		}

		return TRUE;
	}

    // 执行特定的钩子程序
	protected function _run_hook($data)
	{
		// 这个$data会有 类名 方法名 参数 类文件
		if (is_callable($data))
		{
			is_array($data)
				? $data[0]->{$data[1]}()
				: $data();

			return TRUE;
		}
		elseif ( ! is_array($data))
		{
			return FALSE;
		}


		// 防止死循环 因为钩子程序里面可能还有钩子 进入死循环
        // in_progress 的存在阻止这种情况
		if ($this->_in_progress === TRUE)
		{
			return;
		}

		// 下面是对钩子的预处理 判断文件 类和方法 设置路径
		if ( ! isset($data['filepath'], $data['filename']))
		{
			return FALSE;
		}

		$filepath = APPPATH.$data['filepath'].'/'.$data['filename'];

		if ( ! file_exists($filepath))
		{
			return FALSE;
		}

		// 确定类和函数
		$class		= empty($data['class']) ? FALSE : $data['class'];
		$function	= empty($data['function']) ? FALSE : $data['function'];
		$params		= isset($data['params']) ? $data['params'] : '';

		if (empty($function))
		{
			return FALSE;
		}

		// 开始正式执行钩子之前 先把当前的hook的状态设为正在运行中
		$this->_in_progress = TRUE;

		// 类+方法
		if ($class !== FALSE)
		{
			// The object is stored?
			if (isset($this->_objects[$class]))
			{
				if (method_exists($this->_objects[$class], $function))
				{
					$this->_objects[$class]->$function($params);
				}
				else
				{
					return $this->_in_progress = FALSE;
				}
			}
			else
			{
				class_exists($class, FALSE) OR require_once($filepath);

				if ( ! class_exists($class, FALSE) OR ! method_exists($class, $function))
				{
					return $this->_in_progress = FALSE;
				}

				// Store the object and execute the method
				$this->_objects[$class] = new $class();
				$this->_objects[$class]->$function($params);
			}
		}
		// 纯方法
		else
		{
			function_exists($function) OR require_once($filepath);

			if ( ! function_exists($function))
			{
				return $this->_in_progress = FALSE;
			}

			$function($params);
		}

		$this->_in_progress = FALSE;
		return TRUE;
	}

}

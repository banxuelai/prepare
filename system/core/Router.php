<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Router类
 * 大量代码处理的是自定义路由，该类支撑以下几个功能点：
 * 1、自定义路由规则
 * 2、支持回调函数
 * 3、支持使用HTTP动词
 */
class CI_Router {

	public $config;

	// 路由列表
	public $routes =	array();
	//类
	public $class =		'';
	//方法
	public $method =	'index';


	public $directory;
	public $default_controller;

	public $translate_uri_dashes = FALSE;

	public $enable_query_strings = FALSE;

	/*
	 * 构造函数
	 */
	public function __construct($routing = NULL)
	{
		$this->config =& load_class('Config', 'core');
		$this->uri =& load_class('URI', 'core');

		$this->enable_query_strings = ( ! is_cli() && $this->config->item('enable_query_strings') === TRUE);

		// 如果在index.php里指定控制器目录 那么动态路由将这个设置作为控制器的目录
        // 也就是说路由器在找控制器和方法时 在controller下/设置目录/ 下找

		is_array($routing) && isset($routing['directory']) && $this->set_directory($routing['directory']);

		// 核心功能  解析URI到$this->directory $this->class $this->method
		$this->_set_routing();


		if (is_array($routing))
		{
			empty($routing['controller']) OR $this->set_class($routing['controller']);
			empty($routing['function'])   OR $this->set_method($routing['function']);
		}

		log_message('info', 'Router Class Initialized');
	}

    /*
     * 设置路由
     */
	protected function _set_routing()
	{

	    // 加载路由配置文件route.php
		if (file_exists(APPPATH.'config/routes.php'))
		{
			include(APPPATH.'config/routes.php');
		}

		// 对应环境下的路由配置文件router.php
		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/routes.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/routes.php');
		}

		// 设置默认控制器
		if (isset($route) && is_array($route))
		{
			isset($route['default_controller']) && $this->default_controller = $route['default_controller'];
			isset($route['translate_uri_dashes']) && $this->translate_uri_dashes = $route['translate_uri_dashes'];
			unset($route['default_controller'], $route['translate_uri_dashes']);
			$this->routes = $route;
		}


		if ($this->enable_query_strings)
		{

			if ( ! isset($this->directory))
			{
				$_d = $this->config->item('directory_trigger');
				$_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';

				if ($_d !== '')
				{
					$this->uri->filter_uri($_d);
					$this->set_directory($_d);
				}
			}

			$_c = trim($this->config->item('controller_trigger'));
			if ( ! empty($_GET[$_c]))
			{
				$this->uri->filter_uri($_GET[$_c]);
				$this->set_class($_GET[$_c]);

				$_f = trim($this->config->item('function_trigger'));
				if ( ! empty($_GET[$_f]))
				{
					$this->uri->filter_uri($_GET[$_f]);
					$this->set_method($_GET[$_f]);
				}

				$this->uri->rsegments = array(
					1 => $this->class,
					2 => $this->method
				);
			}
			else
			{
				$this->_set_default_controller();
			}

			return;
		}

		// 非querystring模式的走这里
		if ($this->uri->uri_string !== '')
		{
			$this->_parse_routes();
		}
		else
		{
			$this->_set_default_controller();
		}
	}

    /**
     * 看，这里有调用Router::_validate_request();而Router::_validate_request()的作用是检测寻找出一个
     * 正确存在的路由，并确定它，确定后的值分别放到Rouer::$class这些属性里面。所以使到这个_set_request()也有
     * 这种确定路由的功能。
     *
     * 注：
     * $segments=$this->_validate_request($segments); 等式右边，括号里面的这个$segments，也就是调用
     * _set_request()时传入来的这个参数，它有这样的特点：
     * 1）如果这时_set_request()是在Router::_set_default_controller()中调用的话，那个这个$segments是永远不会为
     *  空数组，嗯，绝对不会。
     *
     * 而左边这个$segments的值，经过下面这行代码后，要么为空数组array(),要么为确定路由后的段数组。
     * 为空数组的原因是，$this->_validate_request();里面没有找到当前目录的默认控制器。此时，右边的
     * $seg
     */
	protected function _set_request($segments = array())
	{
		$segments = $this->_validate_request($segments);
		// If we don't have any segments left - try the default controller;
		// WARNING: Directories get shifted out of the segments array!
		if (empty($segments))
		{
			$this->_set_default_controller();
			return;
		}

		if ($this->translate_uri_dashes === TRUE)
		{
			$segments[0] = str_replace('-', '_', $segments[0]);
			if (isset($segments[1]))
			{
				$segments[1] = str_replace('-', '_', $segments[1]);
			}
		}

		$this->set_class($segments[0]);
		if (isset($segments[1]))
		{
			$this->set_method($segments[1]);
		}
		else
		{
			$segments[1] = 'index';
		}

		array_unshift($segments, NULL);
		unset($segments[0]);
		$this->uri->rsegments = $segments;
	}

	// --------------------------------------------------------------------


    //  设置默认控制器
	protected function _set_default_controller()
	{
	    // 如果没有默认的话 就报错 结束程序
		if (empty($this->default_controller))
		{
			show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
		}

		// 解析默认
		if (sscanf($this->default_controller, '%[^/]/%s', $class, $method) !== 2)
		{
			$method = 'index';
		}

		// 默认控制器文件不存在则退出
		if ( ! file_exists(APPPATH.'controllers/'.$this->directory.ucfirst($class).'.php'))
		{
			return;
		}

		$this->set_class($class);
		$this->set_method($method);

		$this->uri->rsegments = array(
			1 => $class,
			2 => $method
		);

		log_message('debug', 'No URI present. Default controller set.');
	}


	// 路由目录逐级往下找
	protected function _validate_request($segments)
	{
		$c = count($segments);
		$directory_override = isset($this->directory);

		// 支撑多级目录
		while ($c-- > 0)
		{
			$test = $this->directory
				.ucfirst($this->translate_uri_dashes === TRUE ? str_replace('-', '_', $segments[0]) : $segments[0]);

			if ( ! file_exists(APPPATH.'controllers/'.$test.'.php')
				&& $directory_override === FALSE
				&& is_dir(APPPATH.'controllers/'.$this->directory.$segments[0])
			)
			{
				$this->set_directory(array_shift($segments), TRUE);
				continue;
			}

			return $segments;
		}


		return $segments;
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Routes
	 *
	 * Matches any routes that may exist in the config/routes.php file
	 * against the URI to determine if the class/method need to be remapped.
	 *
	 * @return	void
	 */
	protected function _parse_routes()
	{
		$uri = implode('/', $this->uri->segments);
		$http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

		// Loop through the route array looking for wildcards
		foreach ($this->routes as $key => $val)
		{
			// Check if route format is using HTTP verbs
			if (is_array($val))
			{
				$val = array_change_key_case($val, CASE_LOWER);
				if (isset($val[$http_verb]))
				{
					$val = $val[$http_verb];
				}
				else
				{
					continue;
				}
			}

			// Convert wildcards to RegEx
			$key = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);

			// Does the RegEx match?
			if (preg_match('#^'.$key.'$#', $uri, $matches))
			{
				// Are we using callbacks to process back-references?
				if ( ! is_string($val) && is_callable($val))
				{
					// Remove the original string from the matches array.
					array_shift($matches);

					// Execute the callback using the values in matches as its parameters.
					$val = call_user_func_array($val, $matches);
				}
				// Are we using the default routing method for back-references?
				elseif (strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE)
				{
					$val = preg_replace('#^'.$key.'$#', $val, $uri);
				}

				$this->_set_request(explode('/', $val));
				return;
			}
		}

	    // 设置网站默认路哟
		$this->_set_request(array_values($this->uri->segments));
	}

	// --------------------------------------------------------------------

	/**
	 * Set class name
	 *
	 * @param	string	$class	Class name
	 * @return	void
	 */
	public function set_class($class)
	{
		$this->class = str_replace(array('/', '.'), '', $class);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the current class
	 *
	 * @deprecated	3.0.0	Read the 'class' property instead
	 * @return	string
	 */
	public function fetch_class()
	{
		return $this->class;
	}

	// --------------------------------------------------------------------

	/**
	 * Set method name
	 *
	 * @param	string	$method	Method name
	 * @return	void
	 */
	public function set_method($method)
	{
		$this->method = $method;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the current method
	 *
	 * @deprecated	3.0.0	Read the 'method' property instead
	 * @return	string
	 */
	public function fetch_method()
	{
		return $this->method;
	}

	// --------------------------------------------------------------------

	/**
	 * Set directory name
	 *
	 * @param	string	$dir	Directory name
	 * @param	bool	$append	Whether we're appending rather than setting the full value
	 * @return	void
	 */
	public function set_directory($dir, $append = FALSE)
	{
		if ($append !== TRUE OR empty($this->directory))
		{
			$this->directory = str_replace('.', '', trim($dir, '/')).'/';
		}
		else
		{
			$this->directory .= str_replace('.', '', trim($dir, '/')).'/';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch directory
	 *
	 * Feches the sub-directory (if any) that contains the requested
	 * controller class.
	 *
	 * @deprecated	3.0.0	Read the 'directory' property instead
	 * @return	string
	 */
	public function fetch_directory()
	{
		return $this->directory;
	}

}

<?php


/**
 * 这个BASEPATH，就是在入口文件(index.php)里面定义的那个BASEPATH～
 * 如果没有定义BASEPATH，那么直接退出，下面程序都不执行。
 * 其实除了入口文件index.php开头没有这句话之外，所有文件都会有这句话
 * 也就是说，所有文件都不能单独运行，一定是index.php在运行过程中把这些文件通
 * 过某种方式引进来运行，所以只有入口文件index.php才能被访问。
*/

defined('BASEPATH') OR exit('No direct script access allowed');


	const CI_VERSION = '3.1.9';

	//加载框架常量
	if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/constants.php'))
	{
		require_once(APPPATH.'config/'.ENVIRONMENT.'/constants.php');
	}

	if (file_exists(APPPATH.'config/constants.php'))
	{
		require_once(APPPATH.'config/constants.php');
	}


    //加载全局函数库
	require_once(BASEPATH.'core/Common.php');


/*
 * ------------------------------------------------------
 * Security procedures
 * ------------------------------------------------------
 */

if ( ! is_php('5.4'))
{
	ini_set('magic_quotes_runtime', 0);

	if ((bool) ini_get('register_globals'))
	{
		$_protected = array(
			'_SERVER',
			'_GET',
			'_POST',
			'_FILES',
			'_REQUEST',
			'_SESSION',
			'_ENV',
			'_COOKIE',
			'GLOBALS',
			'HTTP_RAW_POST_DATA',
			'system_path',
			'application_folder',
			'view_folder',
			'_protected',
			'_registered'
		);

		$_registered = ini_get('variables_order');
		foreach (array('E' => '_ENV', 'G' => '_GET', 'P' => '_POST', 'C' => '_COOKIE', 'S' => '_SERVER') as $key => $superglobal)
		{
			if (strpos($_registered, $key) === FALSE)
			{
				continue;
			}

			foreach (array_keys($$superglobal) as $var)
			{
				if (isset($GLOBALS[$var]) && ! in_array($var, $_protected, TRUE))
				{
					$GLOBALS[$var] = NULL;
				}
			}
		}
	}
}


//自定义错误 异常和程序完成的函数
	set_error_handler('_error_handler');
	set_exception_handler('_exception_handler');
	register_shutdown_function('_shutdown_handler');
/*
a、设置错误处理：set_error_handler('_error_handler')。
处理函数原型：function _error_handler($severity, $message, $filepath, $line)。
程序本身原因或手工触发trigger_error("A custom error has been triggered");
b、设置异常处理：set_exception_handler('_exception_handler')。
处理函数原型：function _exception_handler($exception)。
当用户抛出异常时触发throw new Exception('Exception occurred');
c、千万不要被shutdown迷惑：register_shutdown_function('_shutdown_handler')可以这样理解调用条件：
当页面被用户强制停止时、当程序代码运行超时时、当php代码执行完成时。
 */



	if ( ! empty($assign_to_config['subclass_prefix']))
	{
		get_config(array('subclass_prefix' => $assign_to_config['subclass_prefix']));
	}

    //加载composer
	if ($composer_autoload = config_item('composer_autoload'))
	{
        if ($composer_autoload === TRUE)
		{
			file_exists(APPPATH.'vendor/autoload.php')
				? require_once(APPPATH.'vendor/autoload.php')
				: log_message('error', '$config[\'composer_autoload\'] is set to TRUE but '.APPPATH.'vendor/autoload.php was not found.');
		}
		elseif (file_exists($composer_autoload))
		{
			require_once($composer_autoload);
		}
		else
		{
			log_message('error', 'Could not find the specified $config[\'composer_autoload\'] path: '.$composer_autoload);
		}
	}

	# 到这里，CI框架的基本环境配置初始化已经算完成了，接下来，CodeIgniter会借助一系列的组件，完成更多的需求


    //Benchmark是基准点的意思，他很简单，就是计算任意两点之间程序的运行时间
    //计算程序运行消耗的时间和内存
    //$BM->mark 记录当前位置的时间点 通过两个时间点 计算出时间
	$BM =& load_class('Benchmark', 'core');
	$BM->mark('total_execution_time_start');
	$BM->mark('loading_time:_base_classes_start');


	// 钩子Hooks组件 可以很好地扩展和改造CI
    // 可以这么理解 一个应用从运行到结束这个期间，CI为我们保留了一些位置实现钩子（也就是一段代码）
    // 在应用运行过程中，当运行到可以放钩子的位置，如果有就运行它
	$EXT =& load_class('Hooks', 'core');


	$EXT->call_hook('pre_system');


	//加载配置组件
	$CFG =& load_class('Config', 'core');

	//如果index.php定义配置数组，那么丢给配置组件，统一管理
	if (isset($assign_to_config) && is_array($assign_to_config))
	{
		foreach ($assign_to_config as $key => $value)
		{
			$CFG->set_item($key, $value);
		}
	}

/*
 * 处理框架字符集问题
 */
	$charset = strtoupper(config_item('charset'));
	ini_set('default_charset', $charset);

	if (extension_loaded('mbstring'))
	{
		define('MB_ENABLED', TRUE);
		// mbstring.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('mbstring.internal_encoding', $charset);
		// This is required for mb_convert_encoding() to strip invalid characters.
		// That's utilized by CI_Utf8, but it's also done for consistency with iconv.
		mb_substitute_character('none');
	}
	else
	{
		define('MB_ENABLED', FALSE);
	}

	// There's an ICONV_IMPL constant, but the PHP manual says that using
	// iconv's predefined constants is "strongly discouraged".
	if (extension_loaded('iconv'))
	{
		define('ICONV_ENABLED', TRUE);
		// iconv.internal_encoding is deprecated starting with PHP 5.6
		// and it's usage triggers E_DEPRECATED messages.
		@ini_set('iconv.internal_encoding', $charset);
	}
	else
	{
		define('ICONV_ENABLED', FALSE);
	}

	if (is_php('5.6'))
	{
		ini_set('php.internal_encoding', $charset);
	}

/*
 * ------------------------------------------------------
 *  Load compatibility features
 * ------------------------------------------------------
 */

	require_once(BASEPATH.'core/compat/mbstring.php');
	require_once(BASEPATH.'core/compat/hash.php');
	require_once(BASEPATH.'core/compat/password.php');
	require_once(BASEPATH.'core/compat/standard.php');

    // UTF8组件
	$UNI =& load_class('Utf8', 'core');

    // URI组件
	$URI =& load_class('URI', 'core');

    // 路由Router组件
	$RTR =& load_class('Router', 'core', isset($routing) ? $routing : NULL);

    // 输出组件
	$OUT =& load_class('Output', 'core');


    //下面是输出缓存的处理，这里允许我们自己写个hook来取替代CI原来Output类的缓存输出
    //如果缓存命中则输出，并结束整个CI的单次生命周期。如果没有命中缓存，或没有启用缓存，那么将继续向下执行。
    if ($EXT->call_hook('cache_override') === FALSE && $OUT->_display_cache($CFG, $URI) === TRUE)
	{
		exit;
	}

    //安全组件
	$SEC =& load_class('Security', 'core');

    //安全组件的好基友INPUT组件（主要结合安全组件做一些输入方面的安全处理）
	$IN	=& load_class('Input', 'core');

    //语言组件
	$LANG =& load_class('Lang', 'core');


	//引入控制器父类文件
	require_once BASEPATH.'core/Controller.php';

	function &get_instance()
	{
		return CI_Controller::get_instance();
	}

	// 控制器父类通过前缀方式进行扩展
    // 引入自定义扩展controller
	if (file_exists(APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php'))
	{
		require_once APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php';
	}

	// 这里mark一下，说明CI的所需要的基本的类都加载完了
	$BM->mark('loading_time:_base_classes_end');

	// class 与 method 验证

	$e404 = FALSE;
	$class = ucfirst($RTR->class);
	$method = $RTR->method;

	if (empty($class) OR ! file_exists(APPPATH.'controllers/'.$RTR->directory.$class.'.php'))
	{
		$e404 = TRUE;
	}
	else
	{
		require_once(APPPATH.'controllers/'.$RTR->directory.$class.'.php');

		if ( ! class_exists($class, FALSE) OR $method[0] === '_' OR method_exists('CI_Controller', $method))
		{
			$e404 = TRUE;
		}
		elseif (method_exists($class, '_remap'))
		{
			$params = array($method, array_slice($URI->rsegments, 2));
			$method = '_remap';
		}
		elseif ( ! method_exists($class, $method))
		{
			$e404 = TRUE;
		}
		/**
		 * DO NOT CHANGE THIS, NOTHING ELSE WORKS!
		 *
		 * - method_exists() returns true for non-public methods, which passes the previous elseif
		 * - is_callable() returns false for PHP 4-style constructors, even if there's a __construct()
		 * - method_exists($class, '__construct') won't work because CI_Controller::__construct() is inherited
		 * - People will only complain if this doesn't work, even though it is documented that it shouldn't.
		 *
		 * ReflectionMethod::isConstructor() is the ONLY reliable check,
		 * knowing which method will be executed as a constructor.
		 */
		elseif ( ! is_callable(array($class, $method)))
		{
			$reflection = new ReflectionMethod($class, $method);
			if ( ! $reflection->isPublic() OR $reflection->isConstructor())
			{
				$e404 = TRUE;
			}
		}
	}

	if ($e404)
	{
		if ( ! empty($RTR->routes['404_override']))
		{
			if (sscanf($RTR->routes['404_override'], '%[^/]/%s', $error_class, $error_method) !== 2)
			{
				$error_method = 'index';
			}

			$error_class = ucfirst($error_class);

			if ( ! class_exists($error_class, FALSE))
			{
				if (file_exists(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php'))
				{
					require_once(APPPATH.'controllers/'.$RTR->directory.$error_class.'.php');
					$e404 = ! class_exists($error_class, FALSE);
				}
				// Were we in a directory? If so, check for a global override
				elseif ( ! empty($RTR->directory) && file_exists(APPPATH.'controllers/'.$error_class.'.php'))
				{
					require_once(APPPATH.'controllers/'.$error_class.'.php');
					if (($e404 = ! class_exists($error_class, FALSE)) === FALSE)
					{
						$RTR->directory = '';
					}
				}
			}
			else
			{
				$e404 = FALSE;
			}
		}

		// Did we reset the $e404 flag? If so, set the rsegments, starting from index 1
		if ( ! $e404)
		{
			$class = $error_class;
			$method = $error_method;

			$URI->rsegments = array(
				1 => $class,
				2 => $method
			);
		}
		else
		{
			show_404($RTR->directory.$class.'/'.$method);
		}
	}

	if ($method !== '_remap')
	{
		$params = array_slice($URI->rsegments, 2);
	}

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
	$re = $EXT->call_hook('pre_controller');

    // mark
	$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_start');

    //折腾了这么久，终于实例化我们想要的控制器了
    //终于调用了！！！！！！！！！！！就在这里。
    //不过，不是打击你，虽然我们请求的控制器的那个方法被调用了，但是实际上，我们想要的输出并没有完全输出来。
    //这就是因为$this->load->view();并不是马上输出结果，而是把结果放到缓冲区，然后最后Output类把它冲出来。

	$CI = new $class();


	$EXT->call_hook('post_controller_constructor');

    //现在，所有的请求都会被定位到改控制器的index()中去了。如果_remap不存在，则调用实际控制器的$method方法
    //call_user_func_array 调用回调函数，并把一个数组参数作为回调函数的参数，call_user_func_array 函数和 call_user_func 很相似
    //只是使用了数组的传递参数形式，让参数的结构更清晰。
	call_user_func_array(array(&$CI, $method), $params);

	// Mark a benchmark end point
	$BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');

	# controller_execution_time_( User / check_user_white )_start

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
	$EXT->call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
	if ($EXT->call_hook('display_override') === FALSE)
	{
		$OUT->_display();
	}


	$EXT->call_hook('post_system');


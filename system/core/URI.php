<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 解析uri，确定路由
 * URI类主要处理地址字符串，将uri分解成对应的片段，存到segments数组中。
 * querystring 分解后存到$_GET数组
 * ROUTER路由类在之后的解析路由动作中，也主要依靠URI类的segments属性数组来获取当前上下文的请求URI信息。
 */

class CI_URI {

    //缓存url片段
	public $keyval = array();

	//当前的uri片段
	public $uri_string = '';

	//uri片段数组 数组键值从1开始
	public $segments = array();

    //重建索引的片段数组 数组键值从1开始
	public $rsegments = array();


	//URI段允许PCRE字符组
	protected $_permitted_uri_chars;


	//构造函数 需要获取config文件中的配置
	public function __construct()
	{
		$this->config =& load_class('Config', 'core');

		// If query strings are enabled, we don't need to parse any segments.
		// However, they don't make sense under CLI.
		if (is_cli() OR $this->config->item('enable_query_strings') !== TRUE)
		{
			$this->_permitted_uri_chars = $this->config->item('permitted_uri_chars');

			// If it's a CLI request, ignore the configuration
			if (is_cli())
			{
				$uri = $this->_parse_argv();
			}
			else
			{
				$protocol = $this->config->item('uri_protocol');
				empty($protocol) && $protocol = 'REQUEST_URI';
				switch ($protocol)
				{
					case 'AUTO': // For BC purposes only
					case 'REQUEST_URI':
                        //这种REQUEST_URI方式相对复杂一点，因此封装在$this->_parse_request_uri()；里面。
                        //其实大多数情况下，利用REQUEST URI和SCRIPT NAME都会得到我们想要的路径信息了。
                         $uri = $this->_parse_request_uri();
						break;
					case 'QUERY_STRING':
                        //如果是用QUERY_STRING的话，路径格式一般为index.php?/controller/method/xxx/xxx
                        $uri = $this->_parse_query_string();
						break;
					case 'PATH_INFO':
					default:
						$uri = isset($_SERVER[$protocol])
							? $_SERVER[$protocol]
							: $this->_parse_request_uri();
						break;
				}
			}

			$this->_set_uri_string($uri);
		}

		log_message('info', 'URI Class Initialized');
	}


    // 解析$url填充到$this->segments数组中
	protected function _set_uri_string($str)
	{
		// 移除$str不可见字符
		$this->uri_string = trim(remove_invisible_characters($str, FALSE), '/');

		if ($this->uri_string !== '')
		{
			// Remove the URL suffix, if present
			if (($suffix = (string) $this->config->item('url_suffix')) !== '')
			{
				$slen = strlen($suffix);

				if (substr($this->uri_string, -$slen) === $suffix)
				{
					$this->uri_string = substr($this->uri_string, 0, -$slen);
				}
			}

			$this->segments[0] = NULL;
			// Populate the segments array
			foreach (explode('/', trim($this->uri_string, '/')) as $val)
			{
				$val = trim($val);
				// Filter segments for security
				$this->filter_uri($val);

				if ($val !== '')
				{
					$this->segments[] = $val;
				}
			}

			unset($this->segments[0]);
		}
	}

	// --------------------------------------------------------------------

	protected function _parse_request_uri()
	{
		if ( ! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']))
		{
			return '';
		}

        //从$_SERVER['REQUEST_URI']取值，解析成$uri和$query两个字符串，分别存储请求的路径和get请求参数
        $uri = parse_url('http://dummy'.$_SERVER['REQUEST_URI']);
		$query = isset($uri['query']) ? $uri['query'] : '';
		$uri = isset($uri['path']) ? $uri['path'] : '';

		if (isset($_SERVER['SCRIPT_NAME'][0]))
		{
			if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
			{
				$uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
			}
			elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
			{
				$uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
			}
		}

        //对于请求服务器的具体URI包含在查询字符串这种情况。
        //例如$uri以?/开头的 ，实际上if条件换种写法就是if(strncmp($uri, '?/', 2) === 0))，类似：
        //http://www.citest.com/index.php?/welcome/index
		if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0)
		{
			$query = explode('?', $query, 2);
			$uri = $query[0];
			$_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
		}
		else
		{
			$_SERVER['QUERY_STRING'] = $query;
		}

		// 将查询字符串按照键名存入$_GET数组
		parse_str($_SERVER['QUERY_STRING'], $_GET);

		if ($uri === '/' OR $uri === '')
		{
			return '/';
		}

        //调用 _remove_relative_directory($uri)函数作安全处理
        //移除$uri中的../相对路径字符和反斜杠
        return $this->_remove_relative_directory($uri);
	}

	// --------------------------------------------------------------------

	protected function _parse_query_string()
	{
		$uri = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');

		if (trim($uri, '/') === '')
		{
			return '';
		}
		elseif (strncmp($uri, '/', 1) === 0)
		{
			$uri = explode('?', $uri, 2);
			$_SERVER['QUERY_STRING'] = isset($uri[1]) ? $uri[1] : '';
			$uri = $uri[0];
		}

		parse_str($_SERVER['QUERY_STRING'], $_GET);

		return $this->_remove_relative_directory($uri);
	}

	// --------------------------------------------------------------------

    // 把每一个命令行参数 假设它是一个URI段
	protected function _parse_argv()
	{
		$args = array_slice($_SERVER['argv'], 1);
		return $args ? implode('/', $args) : '';
	}


    // _remove_relative_directory($uri)函数作安全处理，移除$uri中的../相对路径字符和反斜杠////
	protected function _remove_relative_directory($uri)
	{
		$uris = array();
		$tok = strtok($uri, '/');
		while ($tok !== FALSE)
		{
			if (( ! empty($tok) OR $tok === '0') && $tok !== '..')
			{
				$uris[] = $tok;
			}
			$tok = strtok('/');
		}

		return implode('/', $uris);
	}

	// --------------------------------------------------------------------

    //过滤不合法的url字符，允许的uri是你的配置$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
    public function filter_uri(&$str)
	{
		if ( ! empty($str) && ! empty($this->_permitted_uri_chars) && ! preg_match('/^['.$this->_permitted_uri_chars.']+$/i'.(UTF8_ENABLED ? 'u' : ''), $str))
		{
			show_error('The URI you submitted has disallowed characters.', 400);
		}
	}

	// --------------------------------------------------------------------

    /*
     * 用于从URI中获取指定段 参数n是希望获取的段序号
     * URI的段从左到右进行编号
     */
	public function segment($n, $no_result = NULL)
	{
		return isset($this->segments[$n]) ? $this->segments[$n] : $no_result;
	}

    /*
     * 返回确定路由后的一个uri段
     */
	public function rsegment($n, $no_result = NULL)
	{
		return isset($this->rsegments[$n]) ? $this->rsegments[$n] : $no_result;
	}

	// --------------------------------------------------------------------

	/**
	 * URI to assoc
	 *
	 * Generates an associative array of URI data starting at the supplied
	 * segment index. For example, if this is your URI:
	 *
	 *	example.com/user/search/name/joe/location/UK/gender/male
	 *
	 * You can use this method to generate an array with this prototype:
	 *
	 *	array (
	 *		name => joe
	 *		location => UK
	 *		gender => male
	 *	 )
	 *
	 * @param	int	$n		Index (default: 3)
	 * @param	array	$default	Default values
	 * @return	array
	 */
	public function uri_to_assoc($n = 3, $default = array())
	{
		return $this->_uri_to_assoc($n, $default, 'segment');
	}

	// --------------------------------------------------------------------

	/**
	 * Routed URI to assoc
	 *
	 * Identical to CI_URI::uri_to_assoc(), only it uses the re-routed
	 * segment array.
	 *
	 * @see		CI_URI::uri_to_assoc()
	 * @param 	int	$n		Index (default: 3)
	 * @param 	array	$default	Default values
	 * @return 	array
	 */
	public function ruri_to_assoc($n = 3, $default = array())
	{
		return $this->_uri_to_assoc($n, $default, 'rsegment');
	}

	// --------------------------------------------------------------------

	/**
	 * Internal URI-to-assoc
	 *
	 * Generates a key/value pair from the URI string or re-routed URI string.
	 *
	 * @used-by	CI_URI::uri_to_assoc()
	 * @used-by	CI_URI::ruri_to_assoc()
	 * @param	int	$n		Index (default: 3)
	 * @param	array	$default	Default values
	 * @param	string	$which		Array name ('segment' or 'rsegment')
	 * @return	array
	 */
	protected function _uri_to_assoc($n = 3, $default = array(), $which = 'segment')
	{
		if ( ! is_numeric($n))
		{
			return $default;
		}

		if (isset($this->keyval[$which], $this->keyval[$which][$n]))
		{
			return $this->keyval[$which][$n];
		}

		$total_segments = "total_{$which}s";
		$segment_array = "{$which}_array";

		if ($this->$total_segments() < $n)
		{
			return (count($default) === 0)
				? array()
				: array_fill_keys($default, NULL);
		}

		$segments = array_slice($this->$segment_array(), ($n - 1));
		$i = 0;
		$lastval = '';
		$retval = array();
		foreach ($segments as $seg)
		{
			if ($i % 2)
			{
				$retval[$lastval] = $seg;
			}
			else
			{
				$retval[$seg] = NULL;
				$lastval = $seg;
			}

			$i++;
		}

		if (count($default) > 0)
		{
			foreach ($default as $val)
			{
				if ( ! array_key_exists($val, $retval))
				{
					$retval[$val] = NULL;
				}
			}
		}

		// Cache the array for reuse
		isset($this->keyval[$which]) OR $this->keyval[$which] = array();
		$this->keyval[$which][$n] = $retval;
		return $retval;
	}

	// --------------------------------------------------------------------

	/**
	 * Assoc to URI
	 *
	 * Generates a URI string from an associative array.
	 *
	 * @param	array	$array	Input array of key/value pairs
	 * @return	string	URI string
	 */
	public function assoc_to_uri($array)
	{
		$temp = array();
		foreach ((array) $array as $key => $val)
		{
			$temp[] = $key;
			$temp[] = $val;
		}

		return implode('/', $temp);
	}

	// --------------------------------------------------------------------

	/**
	 * Slash segment
	 *
	 * Fetches an URI segment with a slash.
	 *
	 * @param	int	$n	Index
	 * @param	string	$where	Where to add the slash ('trailing' or 'leading')
	 * @return	string
	 */
	public function slash_segment($n, $where = 'trailing')
	{
		return $this->_slash_segment($n, $where, 'segment');
	}

	// --------------------------------------------------------------------

    //取一个URI路由段斜线
	public function slash_rsegment($n, $where = 'trailing')
	{
		return $this->_slash_segment($n, $where, 'rsegment');
	}

	/*
	 * 该方法和 segment() 类似，只是它会根据第二个参数在返回结果的前面或/和后面添加斜线。
     * 如果第二个参数未设置，斜线会添加到后面根据源代码看，
     * 如果第二个参数不是trailing,也不是leading,将会在头尾都加斜杠。
	 */
	protected function _slash_segment($n, $where = 'trailing', $which = 'segment')
	{
		$leading = $trailing = '/';

		if ($where === 'trailing')
		{
			$leading	= '';
		}
		elseif ($where === 'leading')
		{
			$trailing	= '';
		}

		return $leading.$this->$which($n).$trailing;
	}

	/*
	 * 返回 URI 所有的段组成的数组
	 */
	public function segment_array()
	{
		return $this->segments;
	}

	/*
	 * 返回 URI 所有的段组成的数组
	 */
	public function rsegment_array()
	{
		return $this->rsegments;
	}

    /*
     * 返回URI的总段数
     */
	public function total_segments()
	{
		return count($this->segments);
	}

    /*
     * 返回URI的总段数
     */
	public function total_rsegments()
	{
		return count($this->rsegments);
	}

	// --------------------------------------------------------------------

	/**
	 * 返回一个相对的URI字符串
	 */
	public function uri_string()
	{
		return $this->uri_string;
	}

	// --------------------------------------------------------------------

	/*
	 * 返回一个相对的URI字符串
	 */
	public function ruri_string()
	{
		return ltrim(load_class('Router', 'core')->directory, '/').implode('/', $this->rsegments);
	}

}

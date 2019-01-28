<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('instance'))
{
    /**
     * 功能：获取CI对象的一个完整实例引用
     * @return CI_Controller
     */
    function & instance() {
		return get_instance();
	}
}

/**
 * 功能：获取GET数据
 * @param $k
 * @return string
 */
function g($k)
{
	$data = instance()->input->get($k, TRUE);
	if(is_string($data) === TRUE) {
		$data = htmlspecialchars($data, ENT_QUOTES);
	}
	return $data;
}

/**
 * 功能：获取配置文件的参数值
 * @param $key
 * @param null $value
 * @return mixed
 */
function gc($key ,$value = null)
{
	return instance()->config->item( $key ,$value);
}

/**
 * 功能：获取POST数据
 *
 * @access public
 * @param string $k 变量名
 * @return string
 * @author wangyule <wangyule@vread.cn>
 * @date 2013-3-27
 */
function p($k)
{
// 	$chkref = './include/global/chkref.ini';
// 	$refer = _checkrefer($chkref);
//
// 	if($refer === false) {
// 		exit('非法操作');
// 	}

	return instance()->input->post( $k, true );
}


/**
 * 功能：获取URL上的参数
 * @param $n URL的位置
 * @param int $default 默认值
 * @return mixed
 */
function uk( $n, $default = 0 )
{
	return instance()->security->xss_clean(instance()->uri->segment($n, $default));
}


/**
 * 功能：检查referer过滤
 *
 * @access protected
 * @param string $configFile 配置文件路径。必选
 * @return mixed
 * @author wangyule <wangyule@vread.cn>
 * @date 2013-3-27
 */
function _checkrefer($configFile) {
	$ret = false;

	if( isset($_SERVER['HTTP_VREADREFER']) || isset($_SERVER['HTTP_VREADREFERER']))
	{
		return true;
	}

    if(isset($_GET['cli']) && $_GET['cli'] == 'vm:flash:request'){ # flash 没有头信息 fixed
        return true;
    }
	if(isset($_SERVER['HTTP_X_USER_AGENT'])) {
		return true;
	}

	if (empty ( $configFile )) {
		return true;
	}

	$fp = @fopen ( $configFile, "r" );
	$ini_array = array ();
	$inikey = '';

	while ( $line = @fgets ( $fp, 4096 ) ) {
		$line = trim ( $line );
		if (empty ( $line )) {
			continue;
		}

		if (strpos ( $line, '#' ) === 0)
			continue;

		if (preg_match ( "/^\[(.+)\]$/i", $line, $matches )) {
			$inikey = $matches [1];
			continue;
		}

		if (! empty ( $inikey )) {
			$ini_array [$inikey] [] = trim ( $line );
		}

	}

	if (count ( $ini_array ) == 0) {
		return true;
	}
	
	
	$ref =  isset($_SERVER ['HTTP_REFERER']) ? urldecode ( $_SERVER ['HTTP_REFERER'] ) : '';
    if(empty($ref)) { return false; }

	# $ref = urldecode ( $_SERVER ['HTTP_REFERER'] );
	if (strpos ( $ref, 'http://' ) !== 0 && strpos ( $ref, 'https://' ) !== 0) {
		$ref = 'http://' . $ref;
	}

	$refArr = parse_url ( $ref );
	$refhost = $refArr ['host'];
	foreach ( $ini_array ['allow'] as $url ) {
		if (strpos ( $url, 'http://' ) !== 0 && strpos ( $url, 'https://' ) !== 0) {
			$url = 'http://' . $url;
		}

		$urlhostArr = parse_url ( $url );
		$urlhost = $urlhostArr ['host'];

		if (preg_match ( "/^" . preg_quote ( $urlhost, '/' ) . "$/i", $refhost )) {
			$ret = true;
		}

		if ($ret) {
			$urlcomp = $urlhostArr ['scheme'] . '://' . $urlhostArr ['host'] ;
			$urlcomp .= isset($urlhostArr ['path'])?$urlhostArr ['path']:'';
			$refcomp = $refArr ['scheme'] . '://' . $refArr ['host'] ;
			$refcomp .= isset($refArr ['path'])?$refArr ['path']:'';
			$urlreg = "/^" . preg_quote ( $urlcomp, '/' ) . "/i";
			if (preg_match ( $urlreg, $refcomp )) {
				if (strlen ( $refcomp ) != strlen ( $urlcomp ) && substr ( $refcomp, strlen ( $urlcomp ), 1 ) != '/') {
					$ret = false;
				} else {
					break;
				}
			} else {
				$ret = false;
			}
		}
	}

	if ($ret) {
		if (isset ( $ini_array ['ban'] )) {
			foreach ( $ini_array ['ban'] as $reg ) {
				if (preg_match ( "/" . $reg . "/", $ref )) {
					$ret = false;
					break;
				}
			}
		}
	}

	return $ret;
}

/**
 * 截取指定长度的字符串(UTF-8专用 汉字和大写字母长度算1，其它字符长度算0.5)
 * @param $sourcestr 原字符串
 * @param int $cutlength 截取长度
 * @param string $etc 省略字符（...）
 * @return string 截取后的字符串
 */
function msubstr($sourcestr, $cutlength = 1, $etc = '<em class="fma">...</em>')
{
    $returnstr = '';
    $i = 0;
    $n = 0.0;
    $str_length = strlen($sourcestr); //字符串的字节数
    while (($n < $cutlength) and ($i < $str_length)) {
        $temp_str = substr($sourcestr, $i, 1);
        $ascnum = ord($temp_str); //得到字符串中第$i位字符的ASCII码
        if ($ascnum >= 252) { //如果ASCII位高与252
            $returnstr = $returnstr . substr($sourcestr, $i, 6); //根据UTF-8编码规范，将6个连续的字符计为单个字符
            $i = $i + 6; //实际Byte计为6
            $n++; //字串长度计1
        } elseif ($ascnum >= 248) { //如果ASCII位高与248
            $returnstr = $returnstr . substr($sourcestr, $i, 5); //根据UTF-8编码规范，将5个连续的字符计为单个字符
            $i = $i + 5; //实际Byte计为5
            $n++; //字串长度计1
        } elseif ($ascnum >= 240) { //如果ASCII位高与240
            $returnstr = $returnstr . substr($sourcestr, $i, 4); //根据UTF-8编码规范，将4个连续的字符计为单个字符
            $i = $i + 4; //实际Byte计为4
            $n++; //字串长度计1
        } elseif ($ascnum >= 224) { //如果ASCII位高与224
            $returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i = $i + 3; //实际Byte计为3
            $n++; //字串长度计1
        } elseif ($ascnum >= 192) { //如果ASCII位高与192
            $returnstr = $returnstr . substr($sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i = $i + 2; //实际Byte计为2
            $n++; //字串长度计1
        } elseif ($ascnum >= 65 and $ascnum <= 90 and $ascnum != 73) { //如果是大写字母 I除外
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，大写字母计成一个高位字符
        } elseif (!(array_search($ascnum, array(37, 38, 64, 109, 119)) === FALSE)) { //%,&,@,m,w 字符按１个字符宽
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，这些字条计成一个高位字符
        } else { //其他情况下，包括小写字母和半角标点符号
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数计1个
            $n = $n + 0.5; //其余的小写字母和半角标点等与半个高位字符宽...
        }
    }
    if ($i < $str_length) {
        $returnstr = $returnstr . $etc; //超过长度时在尾处加上省略号
    }
    return $returnstr;
}
function getMca()
{
    $RTR =& load_class('Router', 'core');
    $m = trim($RTR->fetch_directory(), '/');
    $c = $RTR->fetch_class();
    $a = $RTR->fetch_method();
    $mca = "{$m}/{$c}/{$a}";
    return compact('m', 'c', 'a', 'mca');
}

/**
 * 转义双引号
 * @param $content
 * @return mixed
 */
function convertQuotes($content)
{
    return str_replace(array('\f', '\n', '\r', '\t', '\v'), '', $content);
}
if ( ! function_exists('arrayColumnValues')) {
    function arrayColumnValues($arr, $key)
    {
        $a = array();
        if(empty($arr) || !is_array($arr)) { return $a; }
        if(is_array($key)) {
            foreach($arr as $row) {
                foreach($key as $k) {
                    if(! isset($row[$k])) { continue; }
                    $a[] = $row[$k];
                }
            }
        }
        elseif (is_string($key)) {
            foreach($arr as $row) {
                if(! isset($row[$key])) { continue; }
                $a[] = $row[$key];
            }
        }
        return array_unique($a);
    }
}

if (! function_exists ('getIp'))
{
    /**
     * 获取ip
     * @return string
     */
    function getIp()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
        {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
        {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
        {
            $ip = getenv('REMOTE_ADDR');
        }
        elseif (isset($_SERVER ['REMOTE_ADDR']) && $_SERVER ['REMOTE_ADDR'] && strcasecmp($_SERVER ['REMOTE_ADDR'], 'unknown'))
        {
            $ip = $_SERVER ['REMOTE_ADDR'];
        }
        preg_match("/[\d\.]{7,15}/", $ip, $ipMatches);
        $ip = $ipMatches[0] ? $ipMatches[0] : 'unknown';
        unset($ipMatches);

        return $ip;
    }
}

if ( ! function_exists('exceptionHandler'))
{
    /**
     * 捕获exception记录日志
     * @param $exception
     */
	function exceptionHandler($exception)
    {
		$message = sprintf('Exception报告异常信息：%s 异常代码：%s 于文件：%s 行：%s',
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getLine(),
            $exception->getFile()
        );
                // FOR debug (by zhanhengmin@vcomic.com)
                $_error =& load_class('Exceptions', 'core');
                $_error->show_php_error($severity = E_USER_NOTICE, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                // --end
		log_message('error', $message, TRUE);
	}
}

if (! function_exists('post'))
{
	/**
	 * 功能：  向服务器发送POST请求
	 *
	 * @access public
	 * @param string $url 要请求的url地址。必选
	 * @param array $post 请求参数。可选
	 * @param array $options curl配置参数。可选
	 * @return mixed
	 * @time 2013-04-02
	 */
    function post($url, array $post = array(), array $options = array())
    {
        $postFields = http_build_query($post, '', '&');
        $defaults = array(
            CURLOPT_POST 			=> 1,
            CURLOPT_HEADER 			=> 0,
            CURLOPT_URL 			=> $url,
            CURLOPT_RETURNTRANSFER 	=> 1,
            CURLOPT_TIMEOUT 		=> 30,
            CURLOPT_CONNECTTIMEOUT	=> 30,
            CURLOPT_POSTFIELDS 		=> $postFields,
            CURLOPT_SSL_VERIFYPEER  => false
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        $result = curl_exec($ch);

        $error = curl_error($ch);
        $info = curl_getinfo($ch);

        if ($error) {
            $errno = curl_errno($ch);
            $logTitle = "[Func]:post(), [POST]:{$postFields}, [CURL_ERROR]: {$error} ({$errno})";
            appLog($logSrv='http', $logCode='http_curl_post_err', $logTitle, $info);
        } else
        {
            if($info['total_time'] > 4)
            {
                $logTitle = "[Func]:post(), [建立连接所消耗的时间]: {$info['connect_time']}, [从建立连接到准备传输所使用的时间]: {$info['pretransfer_time']}, [从建立连接到传输开始所使用的时间]: {$info['starttransfer_time']}";
                appLog($logSrv='http', $logCode='http_curl_post_timeout', $logTitle, $info);
            }
        }

        curl_close($ch);

        return $result;
    }
}

/**
 * 向服务器发送GET请求
 *
 * @param $url 要请求的url地址。必选
 * @param array $get 请求参数。可选
 * @param array $options curl配置参数。可选
 * @param int $timeOut 超时时间
 * @return mixed
 */
function get($url, array $get = array(), array $options = array(), $timeOut=30)
{
    if(! empty($get)) {
        $url .= (strpos($url, '?') === FALSE ? '?' : '') . http_build_query($get, '', '&');
    }
    $defaults = array(
        CURLOPT_URL => $url,
        CURLOPT_TIMEOUT => empty($timeOut) ? 30 : intval($timeOut),
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    $result = curl_exec($ch);
    if(curl_error($ch)){
        trigger_error(curl_error($ch));
    }

    $error = curl_error($ch);
    $info = curl_getinfo($ch);

    if ($error) {
        $errno = curl_errno($ch);
        $logTitle = "[Func]:get(), [CURL_ERROR]: {$error} ({$errno})";
        echo($url.PHP_EOL.$logTitle);
        appLog($logSrv='http', $logCode='http_curl_get_err', $logTitle, $info);
    } else
    {
        if($info['total_time'] > 4)
        {
            $logTitle = "[Func]:get(), [建立连接所消耗的时间]: {$info['connect_time']}, [从建立连接到准备传输所使用的时间]: {$info['pretransfer_time']}, [从建立连接到传输开始所使用的时间]: {$info['starttransfer_time']}";
            appLog($logSrv='http', $logCode='http_curl_get_timeout', $logTitle, $info);
        }
    }

    curl_close($ch);

    return $result;
}



if(!function_exists('curl_get_contents')){
	/**
	 * 功能： 向服务器发送GET/POST请求
	 * @param string $url 			# 要请求的url地址。必选
	 * @param array  $get  			# 请求参数。可选 GET/POST
	 * @param array  $options 		# curl配置参数。可选
	 * @param Int    $exptTime      # 请求过期时间
	 */
	function curl_get_contents($url = '', $method = "GET", $data = array(),$exptTime = 30) 
	{
	    # 初始化
		$query  = array();
	    $curl   = curl_init();
	    
	    foreach($data as $k => $v )
	    {
	        $query[] = $k.'='.urlencode($v);
	    }
	    # GET/POST
	    if(strtoupper($method) == 'GET' && $data )
	    {
	        $url .= '?'.implode('&', $query);
	        
	    }elseif(strtoupper($method) == 'POST' && $data )
	    {
	        curl_setopt($curl, CURLOPT_POST, 1);
	        curl_setopt($curl, CURLOPT_POSTFIELDS, implode('&', $query));
	    }
	    
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_TIMEOUT, $exptTime);
	    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,120);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	  
	    # 执行  
	    $output = curl_exec($curl);
	    curl_close($curl);
	    
	    return $output;
	}
}

if(!function_exists('getExpiredTime')){
    /**
     * 获取过期时间
     * @param int $timestamp
     * @param int $m 自然月
     * @return array|bool
     */
    function getExpiredTime($timestamp=0,$m=1)
    {
        if(empty($timestamp)){
            return FALSE;
        }
        $d1 = date("Y-m-d",$timestamp);
        list($d1y,$d1m,$d1d) = explode("-", $d1);
        $d1m = intval($d1m);
        $d2 = date('Y-m-d',strtotime("+{$m} month",$timestamp));

        list($d2y,$d2m,$d2d) = explode("-", $d2);
        $d2m = intval($d2m);
        $d2_timestamp = strtotime($d2);

        if($d2m == $d1m + $m +1 || $d2m+12-$m == $d1m+1){
            $d2_f = date('Y-m-01',$d2_timestamp);
            $d2_f_timestamp = strtotime($d2_f);
            //$d2_f_timestamp = strtotime(date('Y-m-01',$d2_timestamp))-1;
            //$d2_f = date('Y-m-d 23:59:59',$d2_f_timestamp);
            return array('day'=>$d2_f,'timestamp'=>$d2_f_timestamp);
        }else{
            return array('day'=>$d2,'timestamp'=>$d2_timestamp);
        }
    }
}

if (! function_exists ( 'logMsg' ))
{
    function logMsg($var, $key = 'print_r')
    {
        # 纪录日志
        $redis = new Redis();
        $redis->connect(LOG_REDIS_ADD, LOG_REDIS_PORT);
        if(ENVIRONMENT == 'testing') {
            $redis->auth(REDIS_AUTH_PASS);
        }
        $logKey = empty($key) ? 'print_r:'.date('Y:m:d') : $key;
        $redis->lPush("dev:{$logKey}", print_r($var, true));
        $redis->expire($logKey, 20 * 24 * 3600); # 20 tian
    }
}

if (! function_exists ( 'quit' ))
{
    /**
     * 退出（记录超时的脚本）
     * @param string $str
     */
    function quit($str='')
    {
        echo $str;
		global $BM;
		$elapsed = $BM->elapsed_time ( 'total_execution_time_start', 'total_execution_time_end' );
        $elapsed = floatval($elapsed);

		//记录超时
		if( $elapsed > gc('running_timeout') ) {
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
			log_message ( 'error', "脚本运行超时，运行脚本：".$uri." 运行时间：" . $elapsed );
		}
		
		exit();
	}
}

if (! function_exists ('strShortTime'))
{
    /**
     * 返回相对于当前时间的友善短时间串，比如3秒前,10分钟前
     *
     * @param int $time 时间戳
     * @param int $level
     * @return unknown
     */
    function strShortTime($time, $level = 10)
    {
        $timestamp = time();
        $diff = $timestamp - $time;
        if ($diff < 0) {
            $result = '';
        } elseif ($diff >= 0 and $diff < 60) {//1级
            $result = $diff == 0 ? '1 秒前' : $diff . ' 秒前';
        } elseif ($diff >= 60 and $diff < 1800) {//2级
            $result = $level > 1 ? intval($diff / 60) . ' 分钟前' : date('m-d H:i', $time);
        } elseif ($diff >= 1800 and $diff < 3600) {//3级
            $result = $level > 2 ? '半小时前' : date('m-d H:i', $time);
        } elseif ($diff >= 3600 and $diff < 86400) {//4级
            $result = $level > 3 ? intval($diff / 3600) . ' 小时前' : date('m-d H:i', $time);
        } elseif ($diff >= 86400 and $diff < 604800) {//5级
            $result = $level > 4 ? intval($diff / 86400) . ' 天前' : date('m-d H:i', $time);
        } elseif ($diff >= 604800 and $diff < 2592000) {//6级
            $result = $level > 5 ? intval($diff / 604800) . ' 星期前' : date('m-d H:i', $time);
        } elseif ($diff >= 2592000 and $diff < 31536000) {//7级
            $result = $level > 6 ? intval($diff / 2592000) . ' 月前' : date('m-d H:i', $time);
        } elseif ($diff >= 31536000 and $diff < 94608000) {//8级
            $result = $level > 7 ? intval($diff / 31536000) . ' 年前' : date('y-m-d H:i', $time);
        } else {//9级
            $result = $level > 8 ? '很长一段时间以前' : date('y-m-d H:i', $time);
        }
        return $result;
    }
}

if (! function_exists ('sizeFormat'))
{
    /**
     * 自定义函数将字节转换成MB，GB，TB
     * @param $byteSize
     * @return string
     */
    function sizeFormat($byteSize)
    {
        $i = 0;

        # 当$bytesize 大于是1024字节时，开始循环，当循环到第4次时跳出；
        while(abs($byteSize) >= 1024)
        {
            $byteSize = $byteSize / 1024;
            $i++;
            if($i==4) break;
        }

        //将Bytes,KB,MB,GB,TB定义成一维数组；
        $units = array("Bytes","KB","MB","GB","TB");
        $newSize = round($byteSize, 2);
        return "$newSize $units[$i]" ;
    }
}

if (! function_exists ('getQuerySQL'))
{
    function getQuerySQL($isExplain = FALSE)
    {
        $data = array();

        # 配置项save_query_sql保存SQL
        if (gc('save_query_sql') !== true) {
            return $data;
        }

        # 检测DB操作对象
        if (!class_exists('CI_Model') || empty(CI_Model::$dbConn)) {
            return $data;
        }

        # explain结果
        foreach (CI_Model::$dbConn as $dbName => $v)
        {
            foreach ($v as $type => $db) {
                if (count($db->queries) == 0) {
                    continue;
                }

                foreach ($db->queries as $key => $val) {
                    $sql = str_replace(array("\r", "\n"), ' ', $val);
                    $isSelectSQL = (strtolower(substr($sql, 0, 6)) == 'select') ? true : false;

                    # queryTime
                    $queryTime = number_format($db->query_times[$key], 4);
                    $queryTime = (round($queryTime, 7) * 1000); // ms

                    # explain
                    $explain = array();
                    if (($isExplain === TRUE) && ($isSelectSQL == TRUE)) {
                        $r = $db->_execute("explain $sql");
                        while ($row = mysqli_fetch_assoc($r)) {
                            $explain[] = $row;
                        }
                    }

                    $data[] = array(
                        'query_sql' => $sql,
                        'query_db' => "{$dbName}({$type})",
                        'query_time' => $queryTime,
                        'explain' => $explain,
                        'function' => $db->call_methods[$key]
                    );
                }
            }
        }

        return $data;
    }
}

if(! function_exists ('manhuaShutdownFunction'))
{
    if(function_exists('xhprof_enable') && (isset($_GET['_xhprof_']) && $_GET['_xhprof_'] == 'yes')) {
        xhprof_enable();
    }

    function manhuaShutdownFunction()
    {
        $lastErr = error_get_last();
        $errLevelArr = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_COMPILE_WARNING);

        if (isset($lastErr['type']) && in_array($lastErr['type'], $errLevelArr))
        {
            $logTitle = "PHP ERR {$lastErr['type']}";
            appLog($logSrv='php', $logCode='php_500', $logTitle, $lastErr);
        }

        return false;
    }

    register_shutdown_function('manhuaShutdownFunction');
}

if(! function_exists ( 'url' )) {
	/**
	 * 功能：生成url
	 *
	 * @access public
	 * @param string $action 控制器、方法。必选
	 * @param array $params 要传输的get参数。可选
	 * @param string $domain 域名。默认是www。可选
	 * @param bool $case 返回的url全部为小写。默认是TRUE。可选
	 * @return string
	 * @date 2012-3-23
	 */
	function url($action, array $params = array(), $domain = 'site_www', $case = TRUE) {
		$url = gc($domain).$action.($params ? '?'.http_build_query($params, '', '&') : '');

		return $case ? strtolower($url) : $url;
	}
}

function dump($var, $isExit=0)
{
    if(defined('ENVIRONMENT') && ENVIRONMENT == 'production') {
        if(isset($_GET['debug_dump']) && $_GET['debug_dump']==5) {
            echo "<pre>";
            print_r($var);
            echo "</pre>";

            $isExit ? exit() : '';
        }
    } else {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
        $isExit ? exit() : '';
    }
}

/**
 * 获取文件后缀名
 *
 * @param string $file_name
 * @return string
 */
function getFileExt($file_name) {
	$extend = explode(".", $file_name);
	$va = count($extend) - 1;
	return $va > 0 ? strtolower($extend [$va]) : '';
}

/**
 * 功能：获取用户昵称V3
 *
 * @access public
 * @param $sina_user_id 新浪用户uid。必选
 * @param length 截取名称长度 0为不截取
 * @param saet 新浪API句柄。必选
 * @param a_class 默认值为 null  不为空则表示 返回 a 连接的class 否则返回 非a
 * @param target 当返回值为 a 的时候的打开方式
 * @param href  当返回值为 a 的时候的打开连接  是站内还是微博
 * @param is_show_vtype  是否返回 微博认证状态
 * @param is_show_vip  是否返回 微漫画VIP状态
 * @return string
 * @author Songpengfei <Songpengfei@vread.cn>
 * @time 2011-11-25 下午11:06:08
 */
function getSinaNameV3($sina_user_id , $filter = array())
{
    empty($filter['a_class'])		? $filter['a_class'] 		= '' : null ;
    empty($filter['href'])    		? $filter['href'] 			= 'Vmanhua' : null ;
    empty($filter['is_show_vtype']) ? $filter['is_show_vtype']  = 1 : null ;
    empty($filter['is_show_vip'])   ? $filter['is_show_vip'] 	= 1 : null ;



    $sinaUserInfo = array();
    $userLevel = array();
    if(isset($GLOBALS['run_cache_sina_name_v3'][$sina_user_id]) && !empty($GLOBALS['run_cache_sina_name_v3'][$sina_user_id])) {
        $sinaUserInfo = $GLOBALS['run_cache_sina_name_v3'][$sina_user_id]['user'];
        $userLevel =  $GLOBALS['run_cache_sina_name_v3'][$sina_user_id]['user_level'];
    } else {
        $CI = & get_instance ();
        $CI->load->model('user/user_model') ;
        $sinaUserInfo = $CI->user_model->getUserInfoRowByUidWithCache(CACHE_TIME_ONE_DAY, $sina_user_id);

        $userLevel = $CI->user_model->getUserInfoRowByUid($sina_user_id, 'user_level');

        $GLOBALS['run_cache_sina_name_v3'][$sina_user_id]['user'] = $sinaUserInfo;
        $GLOBALS['run_cache_sina_name_v3'][$sina_user_id]['user_level'] = $userLevel;
    }
    if(empty($sinaUserInfo)) { return ''; }

    $href = $filter['href'] == 'Vmanhua' ? gc('site_www').'/' : 'http://weibo.com/' ;

    $data = '';
    if(!empty( $filter['a_class'] )) {
        $data .= "<a class='{$filter['a_class']}' href='{$href}{$sina_user_id}' target='_blank' title='{$sinaUserInfo['sina_nickname']}' >";
    }

    $data .= $sinaUserInfo['sina_nickname'];


    if(isset($filter['is_show_vtype']) && isset($userLevel['user_level']) && ($filter['is_show_vtype'] == 1))
    {
        switch ($userLevel['user_level'])
        {
            case 1:
                $re = 'personal';
                $data .= '<img class="icon_v_yellow" src="' . newStcUrl('a/image/front/common/b.gif') .'">' ;
                break;
            case 2:
                $re = 'organizational';
                $data .= '<img class="icon_v_blue" src="' . newStcUrl('a/image/front/common/b.gif') .'">' ;
                break;
            case 3:
                $re = 'daren';
                $data .= '<img class="icon_daren" src="' . newStcUrl('a/image/front/common/b.gif') . '">' ;
                break;
            default:
                $re = 'none'; //未认证
                break;
        }
    }

    $data .= empty( $filter['a_class'] )  ? '' : '</a>';
    $data .=  ($sinaUserInfo['is_vip'] == 1 && $filter['is_show_vip'] == 1)? '<a target="_blank" href="' . gc('site_www') . '/vip'. '"><span class="ico_vip"></span></a>' : '' ;//vip
    $data .=  ($sinaUserInfo['is_vip'] == 2 && $filter['is_show_vip'] == 1)? '<a target="_blank" href="' . gc('site_www') . '/vip'. '"><span class="ico_vip_g"></span></a>' : '' ;//过期vip

	return $data ;
}

	/*
	 * 获取某日期的上个月的最后一天的时间
	 * @param $dateString  时间(譬如： date('Y-m-d',time()))
	 * @return Date
	 */
	function getLastMonthLastday($dateString = '') 
	{
		$time = time();
		$dateString && $time = strtotime($dateString);
		
	   return date('Y-m-t', strtotime('last month', $time));
	}
	
	/*
	 * 特许权使用费所得 应纳税额算法
	 * 1。特许权使用费所得，适用比例税率，税率为百分之二十。
	 * 2。特许权使用费所得，每次收入为800以及少于800的，不缴税。每次收入不超过四千元的，减除费用八百元；四千元以上的，减除百分之二十的费用，其余额为应纳税所得额。
	 * 3。特许权使用费所得，是指个人提供专利权，商标权，著作权，非专利技术以及其他特许权的使用权取得的所得。（不能当成稿费，因为稿费是要发表在传统媒体上的）
	 * 4。属于同一事项连续取得收入的，以1个月内取得的收入为一次。
	 * 如 (3000 - 800) * 20%     = 440元
	 * 	4 500 * (1 - 20%) * 20% = 720元
	 * @param $income money
	 */
	function tax($income)
	{
		$tax = 0;
		if ($income > 4000) {
			$tax = round($income * ( 1 - 0.2) * 0.2, 2);
		} elseif ($income > 800) {
			$tax = round(($income - 800) * 0.2, 2);
		}
		return $tax;
	}

/**
 * @param $path
 * @param string $size (s | b | m)
 * @return string
 */
function getComicImage($path, $size = 's')
{
    if (empty($path)) {
        $url = newStcUrl('a/v2/image/default_comic_image.jpg');
    } else {
        $url = S3_URL . $path;
    }
    return addImageParam($url, $size);
}

/**
 * 获取漫画的封面
 * @param $path 路径
 * @param string $size 大小 s:45x61 m:96*131 b:210*285 f:80*109 l:90*120
 * @param $isHover
 * @return string
 */
function getComicCover($path, $size = 's', $isHover = FALSE)
{
    $size = in_array($size, array('s', 'm', 'b', 'f', 'l')) ? $size : 's';

    if (empty($path)) {
        if($isHover === TRUE) {
            return $url = newStcUrl('a/new_v3/space2015/images/ink_h.png');
        }
        return $url = newStcUrl('a/new_v3/space2015/images/ink.png');
    }
    else if (preg_match('/^(hcover)/', $path)){

        return S3_URL . $path;
    }
    else if(preg_match('/http\:/', $path) || preg_match('/https\:/', $path)) {

        $url = $path;
    }
    else if (preg_match('/^(cover)/', $path)) {

        $url = S3_URL . $path;
    }
    else {

        $url = S3_URL . 'cover/' . $path;
    }

    return addImageParam($url, $size);
}

/**
 * 获取章节的封面
 * @param $path
 * @param string $size （s:45x61 m:96*131 b:210*285 f:80*109）
 * @return string
 */
function getChapterCover($path, $size = 's')
{
    if (empty($path)) {
        $url = newStcUrl('a/v2/image/front/common/210285.jpg');
    } else {
        if (stripos($path, 'http:') !== FALSE) {
            $url = $path;
        } else {
            $url = S3_URL . 'cover/' . $path;
        }
    }
    return addImageParam($url, $size);
}
/**
 * 给图片路径加参数
 * 如http://static.manhua.weibo.com/image/0245.jpg变成
 * http://static.manhua.weibo.com/image/0245_b.jpg
 * @param $path
 * @param $param
 * @return string
 */
function addImageParam($path, $param)
{
    $extend = explode(".", $path);
    $count = count($extend);
    if ($count > 1 && !empty($param)) {
        if(in_array(substr($extend[$count - 2], -2, 2), array('_s', '_m', '_b', '_f'))) { # 加入穿过来的参数中含有 _s _b .... 等等 先给去掉！！
            $extend[$count - 2] = substr($extend[$count - 2], 0, strlen($extend[$count - 2]) - 2);
        }
        $extend [$count - 2] = $extend [$count - 2] . '_' . $param;
        $path = implode('.', $extend);
    }
    return $path;
}

/**
 * 将二维数组去重
 * @param  $arr 
 * @param  $key
 * @return array
 */
function assocUnique(&$arr,$key)
{
	$rAr=array();
	for($i=0;$i<count($arr);$i++)
	{
		if(!isset($rAr[$arr[$i][$key]]))
		{
			$rAr[$arr[$i][$key]]=$arr[$i];
		}
	}
	
	$arr=array_values($rAr);
}

function ci_array_keys($var)
{
    $arr = array();
    if(! empty($var) && is_array($var)) {
        foreach($var as $k=>$v) {
            $arr[] = $k;
        }
        $arr = empty($arr) ? array() : array_unique($arr);
    }
    return $arr;
}

/**
 * @param string $logCode 报警类型
 * @param array $logMsg  报警详情
 * @param string $logTitle 报警标题
 * @param int $logSrv  定义服务软件名称 php, mysql, mongodb, redis, memcache ....
 */
function sendLog($logCode, $logMsg, $logTitle = '', $logSrv = 0)
{
    appLog($logSrv, $logCode, $logTitle, $logMsg);
}

function appLog($logSrv, $logCode, $logTitle, $logMsg)
{
    if(defined('ENVIRONMENT') && ENVIRONMENT == 'production')
    {
        $httpType = 'http://';

        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $httpType = 'https://';
        }

        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $httpType = 'https://';
        }

        $reqUrl = isset($_SERVER['HTTP_HOST']) ? "{$httpType}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" : implode(' ', $_SERVER['argv']);
        $data = array(
            'log_srv'   => $logSrv,
            'log_code'  => $logCode,
            'log_title' => $logTitle,
            'log_msg'   => $logMsg,
            'log_time'  => time(), //date('Y-m-d H:i:s', time()), //STARTTIME,
            'req_url'   => $reqUrl,
            'client_ip' => getIp(),
            'xhprof_id' => 0
        );

        if(file_exists('/data/applogs/vmhweb'))
        {
            file_put_contents('/data/applogs/vmhweb/vmhweb_app' . date('Ymd') . '.log', json_encode($data) . PHP_EOL, FILE_APPEND);

        } else {
            //
        }
    }
}

/**
 * 记录定制日志
 * @param unknown $code  （1评论3投票4分享5订阅6吐槽7下载8收藏9取消订阅10投月票）、用户UID、作品ID、章节ID(非针对章节为0)
 * @param unknown $uid 用户id
 * @param unknown $comic_id 作品id
 * @param number $chapter_id 章节id
 * @param string $type 应用类型 1代表微博漫画WEB 2代表微博漫画H5 6代表微博漫画客户端iphone  7代表微博漫画客户端android

 */
function writeActionLog($code, $uid, $comic_id, $chapter_id = 0, $type = 1, $path = '/data1/sinawap/var/logs/wapcommon/vmanhua/') {
    if(! (defined('ENVIRONMENT') && ENVIRONMENT == 'production')) {
        return; # 非正式环境不需要记录！
    }
    #创建目录
    if(!file_exists($path)) {
        mkdir($path);
    }
	
	#组合内容
	$current_time = time();
	$content = $code.'|'.$uid.'|'.$comic_id.'|'.$chapter_id.'|'.$current_time.'|'.$type. "\n";
	
	#写入日志
	$date = date("Ymd");
    $file = $path . 'action_' . $date. '.log';
	writeData($file, $content);
	
}

/**
 * 记录阅读日志
 * @param unknown $data
 * @param string $path
 */
function writeReadLog($data, $path = '/data1/sinawap/var/logs/wapcommon/vmanhua/') {
    #创建目录
    if(!file_exists($path)) {
        mkdir($path);
    }
    $content = '';
    foreach($data as $v) {
        $content .= $v.'|';
    }
    $content = trim($content, '|');
    $content .= "\n";

    #写入日志
    $date = date("Ymd");
    $file = $path . 'read_' . $date. '.log';
    writeData($file, $content);
}

/**
 * 向文件写入数据
 * @param unknown $file
 * @param unknown $content
 * @return boolean
 */
function writeData($file, $content)
{
	$fp = fopen($file, 'a+');

    if($fp == false) {
        return false;
    }
    
	$retries = 0;
	$max_retries = 100;
	do{
		if ($retries > 0)
		{
			usleep(rand(1, 10000));
		}
	
		$retries += 1;
	}while (!flock($fp, LOCK_EX) and $retries<= $max_retries);
	
	if ($retries == $max_retries)
	{
		return false;
	}
	
	fwrite($fp, $content);
	flock($fp, LOCK_UN);
	fclose($fp);
	
	return true;
}

if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
           $headers = '';
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
}

if (!function_exists('sinaUid'))
{
    function sinaUid($uid)
    {
        return $uid;
        if(empty($uid)) { return 0; }

        $uid = trim($uid);
        if(is_numeric($uid) && $uid != '0') {
            return $uid;
        }
        return 0;
    }
}

/**
 * Tpl Cache 是否开启
 * @return bool
 */
function setTplCache()
{
    return false; # 默认开启Tpl缓存机制 true 开启 false 关闭
}

/**
 * 浏览器缓存设置
 * @param int $ttl
 * @return bool
 */
function setBrowserCache($ttl=10)
{
    $ttl = intval($ttl);

    if($ttl && !isset($_GET['_clear_mc']))
    {
        $modifiedTime = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
        if($modifiedTime && (strtotime($modifiedTime) + $ttl > time())) {
            header('HTTP/1.1 304');exit();
        }

        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $ttl) . " GMT");
        header("Cache-Control: max-age={$ttl}");
    }
    return true;
}

if (!function_exists('makeSinaBusinessObjectId'))
{
    function makeSinaBusinessObjectId($comicSinaObjectId) {
        $comicSinaObjectId = intval($comicSinaObjectId);
        if(empty($comicSinaObjectId)) {
            return array();
        }
        $zan = "1022:100202read" . $comicSinaObjectId;
        $page = "100202read" . $comicSinaObjectId;
        return array('zan'=>$zan, 'page'=>$page);
    }
}

if (!function_exists('zanComicSinaObjectIdToComicId'))
{
    function zanComicSinaObjectIdToComicId($zanObjectId) {
        $comicSinaObjectId = str_replace("1022:100202read", '', $zanObjectId);
        return $comicSinaObjectId;
    }
}
if (!function_exists('forceRefreshUrl'))
{
    /**
     * 强刷页面
     * @param $url
     * @return mixed
     */
    function forceRefreshUrl($url)
    {
        if($url){
            return get($url, array('_clear_mc'=>1), array(CURLOPT_COOKIE=>"SUE=es%3Dad1c168b650d05d33f0c4a64d4a9cc3d%26ev%3Dv1%26es2%3D1b5d2270f2f51c74050a68fb72889f51%26rs0%3Dic9E5A0kDxknphbGnMu61ojq026Y8f%252FmCaOHUa6qTeAqZNPZ4oXtsKVC1Fe28KU82cIii3meviCpZ1aSX%252BZin99mrYRfr2L2Pz3so3z2FCDGJrzGyZVh0PIyNETT%252BwD6PkY3L77NWQjm2EJiVcmTVNiIxzCqq089e8mMeMZRb6w%253D%26rv%3D0; SUP=cv%3D1%26bt%3D1387956144%26et%3D1388042544%26d%3Dc909%26i%3Dd139%26us%3D1%26vf%3D0%26vt%3D0%26ac%3D41%26st%3D0%26uid%3D3121573262%26name%3D15725775843%2540sina.cn%26nick%3D%25E7%2594%25A8%25E6%2588%25B73121573262%26fmp%3D%26lcp%3D2013-01-25%252014%253A21%253A56; ALF=1390548144"));
        }
        return '';
    }
}


/**
* authCode 字符串加密、解密函数
* @param	string	$txt		字符串
* @param	string	$operation	ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
* @param	string	$key		密钥：数字、字母、下划线
* @param	string	$expiry		过期时间
* @return	string
*/
if (!function_exists('authCode'))
{
	function authCode($string,  $operation = 'ENCODE',  $key = '',  $expiry = 0) 
	{
		# 验证
		if(!isset($string)) return null;
		$key_length = 4;
		$key = md5($key != '' ? $key : 'manhua_weibo@key#');
		$fixedkey = md5($key);
		$egiskeys = md5(substr($fixedkey,  16,  16));
		$runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)),  -$key_length) : substr($string,  0,  $key_length)) : '';
		$keys = md5(substr($runtokey,  0,  16) . substr($fixedkey,  0,  16) . substr($runtokey,  16) . substr($fixedkey,  16));
		$string = $operation == 'ENCODE' ? sprintf('%010d',  $expiry ? $expiry + time() : 0).substr(md5($string.$egiskeys),  0,  16) . $string : base64_decode(substr($string,  $key_length));
	
		$i = 0; $result = '';
		$string_length = strlen($string);
		for($i = 0; $i < $string_length; $i++) {
			$result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
		}
	
		if($operation == 'ENCODE') {
			return $runtokey . str_replace('=',  '',  base64_encode($result));
		} else {
			if((substr($result,  0,  10) == 0 || substr($result,  0,  10) - time() > 0) && substr($result,  10,  16) == substr(md5(substr($result,  26) . $egiskeys),  0,  16)) {
				return substr($result,  26);
			} else {
				return '';
			}
		}
	}
}

/**
 * 生成静态资源地址
 * @param $file
 * @author gengxuliang <gengxuliang@vread.cn>
 * @return string
 */
function staticUrl($file)
{
    $file = ltrim($file,'/');
    return gc('site_static').'/sv'.gc('static_ver').'/new_v3/'.$file;
}

function newStcUrl($file)
{
    if((substr($file, 0, strlen('http://')) == 'http://') || (substr($file, 0, strlen('https://')) == 'https://'))
    {
        return $file;
    }
    
    $file = ltrim($file, '/');

    if(ENVIRONMENT == 'production') {
        // use path
    }
    else {
        // 测试环境中，对于public，默认走未压缩文件，可以通过在url指定use_public＝yes, 走public
        if((g('use_public') != 'yes') && (strpos($file, '/public/') > 0))
        {
            if(strpos($file, '_page.js') > 0) {
                $file = str_replace("/public/", "/scripts/", $file);
            }

            if(strpos($file, '_page.css') > 0) {
                $file = str_replace("/public/", "/styles/", $file);
            }
        }
    }
    
    return gc('site_static') . '/' . $file . '?reversion=' . gc('static_ver');
}

# zhanhengmin@vcomic.com
function wbComicStcUrl($file)
{
    if((substr($file, 0, strlen('http://')) == 'http://') || (substr($file, 0, strlen('https://')) == 'https://'))
    {
        return $file;
    }
    
    $file = ltrim($file, '/');
    if(ENVIRONMENT == 'production') {
        $version = gc('wbcomic_static_ver');
    }else{
        $version = time();
    }
    return gc('site_static_wbcomic') . '/' . $file . '?_=' . $version;
}

/**
 * 数组根据key排序
 * @param $array
 * @param $keys
 * @param string $type
 * @return array
 */
function arrayOrderBy($array, $keys, $type='asc')
{
    $keysValue = $newArray = array();
    foreach ($array as $k=>$v) {
        $keysValue[$k] = $v[$keys];
    }
    if(strtolower($type) == 'asc') {
        asort($keysValue);
    } else{
        arsort($keysValue);
    }
    reset($keysValue);
    foreach ($keysValue as $k=>$v) {
        $newArray[$k] = $array[$k];
    }
    return $newArray;
}

function makeDir($pathString)
{
    $pathArray = explode('/',$pathString);
    $tmpPath = array_shift($pathArray);
    foreach ($pathArray as $val)
    {
        $tmpPath .= "/".$val;
        if(is_dir($tmpPath))
        {
            continue;
        }else
        {
            @mkdir($tmpPath,0777);
        }
    }
    if(is_dir($tmpPath))
    {
        return $tmpPath;
    }else
    {
        return false;
    }
}

function safeValue($value)
{
    return htmlentities($value, ENT_QUOTES, 'utf-8');
}

function intEncode($num)
{
    $secret = 159276386;
    $num = intval($num);

    $cfg = array('0'=>'a', '1'=>'C', '2'=>'d', '3'=>'f', '4'=>'h', '5'=>'M', '6'=>'N', '7'=>'r', '8'=>'S', '9'=>'T');

    return base64_encode(str_replace(array_keys($cfg), array_values($cfg), $secret - $num));
}

function intDecode($string)
{
    $secret = 159276386;
    $string = trim($string);

    $cfg = array('0'=>'a', '1'=>'C', '2'=>'d', '3'=>'f', '4'=>'h', '5'=>'M', '6'=>'N', '7'=>'r', '8'=>'S', '9'=>'T');

    $n = str_replace(array_values($cfg), array_keys($cfg), base64_decode($string));

    return $secret - intval($n);
}

function isImage($imageFile)
{
    $imageFile = trim($imageFile);
    $imgTypeConf = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP);
    if(function_exists('exif_imagetype')) {
        if(in_array(exif_imagetype($imageFile), $imgTypeConf)) {
            return true;
        }
    }else {
        $imgArr = getimagesize($imageFile);
        if(isset($imgArr[2]) && in_array($imgArr[2], $imgTypeConf)) {
            return true;
        }
    }
    return false;
}

//function jsonEncodeUnicode($value)
//{
//    $str = json_encode($value);
//    $str = preg_replace_callback(
//        "#\\\u([0-9a-f]{4})#i",
//        function ($matchs) {
//            return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
//        },
//        $str
//    );
//    return $str;
//}

/**
 * 通过vmanhuaUid 转换到 新浪Id
 *
 * @param $vmanhuaUid
 * @return int
 */
function vmhUidToSinaUid($vmhUid)
{
    /*
    $vmhUid = intval($vmhUid);
    if(empty($vmhUid)) {
        return 0;
    }

    instance()->load->model('user/rpc_user_model');
    $userIdRow = instance()->rpc_user_model->getUserRowByIdWithCache(CACHE_TIME_HALF_HOUR, array($vmhUid, 'sina_user_id'));

    return ! empty($userIdRow['sina_user_id']) ? $userIdRow['sina_user_id'] : 0;
    */

    $vmhUid = intval($vmhUid);
    if (empty($vmhUid)) {
        return 0;
    }

    $query = json_encode(array(
        "query" => array(
            "term" => array(
                "user_id" => $vmhUid,
            )
        )
    ));

    $ci = &get_instance();
    $ci->load->library('Elasticsearch');
    $res = $ci->elasticsearch->slaveEs("vmh_user_201804")->advancedquery("vmh_user_201804", $query);

    if (isset($res["hits"]["total"]) && $res["hits"]["total"] == 1)
    {
        return $res["hits"]["hits"][0]["_source"]["sina_user_id"];
    } else
    {
        $ci->load->model('user/rpc_user_model');
        $userRow = $ci->rpc_user_model->getUserRowByIdWithCache(CACHE_TIME_HALF_HOUR, array($vmhUid, 'sina_user_id'));

        return ! empty($userRow['sina_user_id']) ? $userRow['sina_user_id'] : 0;
    }
}

/**
 * 通过 新浪Id 转换到 vmanhuaUid
 * @param $sinaUid
 * @return int
 */
function sinaUidToVmhUid($sinaUserId)
{
    $sinaUserId = sinaUid($sinaUserId);
    if (empty($sinaUserId)) {
        return 0;
    }

    $query = json_encode(array(
        "query" => array(
            "term" => array(
                "sina_user_id" => $sinaUserId,
            )
        )
    ));

    $ci = &get_instance();
    $ci->load->library('Elasticsearch');
    $res = $ci->elasticsearch->slaveEs("vmh_user_201804")->advancedquery("vmh_user_201804", $query);

    if (isset($res["hits"]["total"]) && $res["hits"]["total"] == 1)
    {
        return $res["hits"]["hits"][0]["_source"]["user_id"];
    } else
    {
        $ci->load->model('user/rpc_user_model');
        $userRow = $ci->rpc_user_model->getUserInfoRowByUidWithCache(CACHE_TIME_HALF_HOUR, array($sinaUserId, 'user_id'));

        return ! empty($userRow['user_id']) ? $userRow['user_id'] : 0;
    }
}

/**关键字写入词库
 * @param string $path
 * @param string $file
 * @param string $keywords
 * @return bool
 */
function writeKeywordsToDic($path='',$file='',$keywords="")
{
    if(empty($path) || empty($file) || empty($keywords)){
        return false;
    }

    if(!is_dir($path))
    {
        $res = mkdir($path, 0777, true);

        if (!$res){
            return false;
        }
    }

    file_put_contents($path.'/'.$file, $keywords."\n", FILE_APPEND);

    return true;

}

/**
 * 发送短信 阿里大于- 淘宝 -最终发送
 * @param $recNum  	String 短信接收号码（必填）
 * @param $smsTemplateCode  String 短信模板（必填）
 * @param $smsParam  Json 短信模板变量（可选）
 * @param $extend  String 公共回传参数（可选）
 *
 * @return mixed|ResultSet|SimpleXMLElement
 */
function sendMessageByAliDayuTaobao($telNum, $smsTemplateCode, $smsParam, $extend)
{
    # 参数过滤
    $extend = trim($extend);
    $smsParam = trim($smsParam);

    $telNum = trim($telNum);
    $smsTemplateCode = trim($smsTemplateCode);

    # 参数验证
    if(empty($telNum) || empty($smsTemplateCode)) {
        return array('code'=>0, 'message'=>'缺少必要参数');
    }

    # 发送短信
    include_once BASEPATH. 'global/aliMessage/TopClient.php';
    include_once BASEPATH. 'global/aliMessage/AlibabaAliqinFcSmsNumSendRequest.php';

    $c = new TopClient;
    $c->appkey = ALI_MESSAGE_APP_KEY;
    $c->secretKey = ALI_MESSAGE_SECRET_KEY;

    $req = new AlibabaAliqinFcSmsNumSendRequest;
    $req->setExtend($extend);
    $req->setSmsType('normal');
    $req->setSmsFreeSignName(ALI_MESSAGE_SIGN_NAME);
    $req->setSmsParam($smsParam);
    $req->setRecNum($telNum);
    $req->setSmsTemplateCode($smsTemplateCode);

    $messageResult = $c->execute($req);
    $r = json_decode($messageResult, TRUE);

    # 检验是否发送成功
    if(isset($r['alibaba_aliqin_fc_sms_num_send_response'])) {
        return array('code'=>1, 'message'=>'短信发送成功！');
    }

    return array('code'=>0, 'message'=>'短信发送失败，请重新尝试！');
}

/**
 * 阿里云发送短信
 * @param $telNum
 * @param $smsTemplateCode
 * @param $smsParam
 *
 * @return mixed|ResultSet|SimpleXMLElement
 */
function sendSms($telNum, $smsTemplateCode, $smsParam)
{
    include_once BASEPATH. '/global/aliMessage/SignatureHelper.php';

    $params = array();

    $accessKeyId = 'LTAIJ6dGXjI9ZFir';
    $accessKeySecret = 'SrykvRiVbH7yfYWmeSXXzXmfOJZXI0';

    $params['PhoneNumbers'] = $telNum;
    $params['SignName'] = '炫果壳';
    $params['TemplateCode'] = $smsTemplateCode;
    $params['TemplateParam'] = $smsParam;
    $params['OutId'] = '';
    $params['SmsUpExtendCode'] = '';

    // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
    if(! empty($params['TemplateParam']) && is_array($params['TemplateParam'])) {
        $params['TemplateParam'] = json_encode($params['TemplateParam']);
    }

    // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
    $helper = new SignatureHelper();

    // 此处可能会抛出异常，注意catch
    try
    {
        $resultObject = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            'dysmsapi.aliyuncs.com',
            array_merge($params, array(
                'RegionId' => 'cn-hangzhou',
                'Action' => 'SendSms',
                'Version' => '2017-05-25',
            ))
        );

        $result = json_decode(json_encode($resultObject), TRUE);

        if(isset($result['Code']) && $result['Code'] == 'OK') {

            # 记录Reids日志
            $message = date('Y-m-d H:i:s') . " | send_vcode | user_tel: {$telNum} |params: sms_temp:{$smsTemplateCode}:vcode:{$smsParam['code']}| result: ". serialize($result);
            logMsg($message, "send_vcode:" . date('Y-m-d') . ":user_tel:{$telNum}");

            return TRUE;
        }

        # 记录Reids日志
        $message = date('Y-m-d H:i:s') . " | send_vcode | user_tel: {$telNum} |params: sms_temp:{$smsTemplateCode}:vcode:{$smsParam['code']}| result: ". serialize($result);
        logMsg($message, "send_vcode:" . date('Y-m-d') . ":user_tel:{$telNum}");

    }catch(Exception $e) {

    }

    return FALSE;
}

/**
 * 赛邮云通信
 * @param $telNum
 * @param $smsTemplateCode
 * @param $vCode
 * @return array
 */
function sendSmsBySaiYou($telNum, $smsTemplateCode, $vCode)
{
    include_once BASEPATH . '/global/SaiYouMess/messagexsend.php';

    $smsConf = array(
        'appid' => '18186',
        'appkey' => 'e77d7a9e416d1579f587248ae35c45f0',
        'sign_type' => 'normal',
    );

    $sms = new MESSAGEXsend($smsConf);
    $sms->setTo($telNum);
    $sms->SetProject($smsTemplateCode);
    $sms->AddVar('code', $vCode);

    // 此处可能会抛出异常，注意catch
    try
    {
        $result = $sms->xsend();

        if(isset($result['status']) && $result['status'] == 'success') {

            # 记录Reids日志
            $message = date('Y-m-d H:i:s') . " | send_vcode | user_tel: {$telNum} |params: sms_temp:{$smsTemplateCode}:vcode:$vCode| result: ". serialize($result);
            logMsg($message, "send_vcode:" . date('Y-m-d') . ":user_tel:{$telNum}");

            return TRUE;
        }

        # 记录Reids日志
        $message = date('Y-m-d H:i:s') . " | send_vcode | user_tel: {$telNum} |params: sms_temp:{$smsTemplateCode}:vcode:$vCode| result: ". serialize($result);
        logMsg($message, "send_vcode:" . date('Y-m-d') . ":user_tel:{$telNum}");

    }catch(Exception $e) {

    }

    return FALSE;
}

/**
 * 检测流图像文件类型
 * @param $binaryImage
 * @return string
 */
function checkBinaryImageType($binaryImage)
{
    $bitsConf = array(
        'jpg' => "\xFF\xD8\xFF",
        'gif' => "GIF",
        'png' => "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a",
//        'BMP' => 'BM',
    );

    foreach ($bitsConf as $type => $bit)
    {
        if (substr($binaryImage, 0, strlen($bit)) === $bit) {
            return $type;
        }
    }

    return '';
}

/**
 * 检测是否存在表情
 * @param $str
 * @return int
 */
function isExistEmoji($str)
{
    $text = json_encode($str); # 暴露出unicode
    return preg_match("/(\\\u[ed][0-9a-f]{3})/i", $text);
}

/**
 * 生成微博动漫用户昵称
 * @param $nickname 用户昵称
 * @param $len      限制的长度
 * @return string
 */
function createWbComicNickname($nickname = '', $len = 14)
{
    $len        = intval($len);
    $nickname   = trim($nickname);

    # 生成随机数
    $rand = substr(implode('', array_map('ord',
        str_split(md5(uniqid('', TRUE)), 1))), 0, 14);

    # 昵称为空则返回默认昵称
    if(empty($nickname)) {
        return '微博动漫' . time();
    }

    # 根据昵称生成昵称
    if(mb_strlen($nickname) >= $len) {
        $userNickName = mb_substr($nickname, 0, 10);
        $userNickName = $userNickName . $rand;
    }

    if(mb_strlen($nickname) < $len) {
        $userNickName = $nickname . $rand;
    }

    return mb_substr($userNickName, 0, 14);
}

/**
 * 生成微博动漫用户昵称(新版)
 * ink娘X院A系xxxxx  多宝喵Y院B系xxxx X Y A B 随机英文字母;xxxx为00001-99999随机整数
 * @return string
 */
function createVComicNickname($preName = '')
{
	$preName = trim($preName);

	# 前缀-随机生成
	if(empty($preName)) {
		$preNameArr = array('ink娘', '多宝喵');
		$preName = $preNameArr[mt_rand(0, count($preNameArr)-1)];
	}
	
	$strArr = range('A', 'Z');
	
	# 院-大写字母随机数
	$college = $strArr[mt_rand(0, count($strArr)-1)];
	
	# 系-大写字母随机数
	$department = $strArr[mt_rand(0, count($strArr)-1)];

	# 后缀-5位随机数
	$num = '';
	$numArr = range(0, 9);

	for ($i = 0; $i < 5; $i++)
	{
		$num .= $numArr[mt_rand(0, count($numArr)-1)];
	}

	return $preName.$college.'院'.$department.'系'.$num;
}

/**
 * 生成唯一订单号
 * @param int $userId
 * @param string $prefix
 * @return string
 */
function createOrderNo($userId, $prefix)
{
    # 定义返回值
    $orderNo = '';

    # 参数验证
    $userId = intval($userId);
    if(empty($userId) || empty($prefix)) {
        return $orderNo;
    }

    # 生成规则（8位时间串 + 10位用户ID串 + 6位随机数串）
    $date = date('Ymd');
    $rand = date('His');
    $userId = str_pad($userId, 10, '0', STR_PAD_LEFT);
    $orderNo = $prefix . $date . $userId . $rand;

    return $orderNo;
}


/**
 * @desc 获取毫秒
 * @return float
 */
function getMillisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

function mhMark($name)
{
    global $BM;
    return $BM->mark($name);
}

function mhElapsedTime($point1 = '', $point2 = '')
{
    global $BM;
    $elapsed = $BM->elapsed_time($point1, $point2, $decimals = 4);

    if(empty($elapsed)){
        return 0;
    }

    $elapsed = $elapsed * 1000; // ms

    $GLOBALS['tj_use_time']["{$point1} --> {$point2}"] = $elapsed;

    return $elapsed;
}

/**
 * 获取二进制文件格式
 * @param  $binaryFile
 * @return string
 * @author jiangheping@vcomic.com
 * @date   2018-08-24
 */
function getFileType($binaryFile)
{
    //$file = fopen($binaryFile, 'rb');
    //$bin  = fread($binaryFile, 2);

    //fclose($file);

    $strInfo  = @unpack("C2chars", $binaryFile);
    $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);

    $fileType = '';
    switch ($typeCode)
    {
        case 255216:
            $fileType = 'jpg';
            break;
        case 13780:
            $fileType = 'png';
            break;
        case 7173:
            $fileType = 'gif';
            break;
        case 6677:
            $fileType = 'bmp';
            break;
        case 7368:
            $fileType = 'mp3';
            break;
        case 8273:
            $fileType = 'wav';
            break;
        case 3533:
            $fileType = 'amr';
            break;
    }
    return $fileType;
}

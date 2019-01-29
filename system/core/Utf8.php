<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * 编码类文件Utf8.php
 */
class CI_Utf8 {

    /*
     * 构造函数 检测是否支持utf8
     */
	public function __construct()
	{
		if (
			defined('PREG_BAD_UTF8_ERROR')
			&& (ICONV_ENABLED === TRUE OR MB_ENABLED === TRUE)
			&& strtoupper(config_item('charset')) === 'UTF-8'
			)
		{
			define('UTF8_ENABLED', TRUE);
			log_message('debug', 'UTF-8 Support Enabled');
		}
		else
		{
			define('UTF8_ENABLED', FALSE);
			log_message('debug', 'UTF-8 Support Disabled');
		}

		log_message('info', 'Utf8 Class Initialized');
	}

    /*
     *  过滤UTF8字符串，因为编码转换成功率不会到100%
     */
	public function clean_string($str)
	{
		if ($this->is_ascii($str) === FALSE)
		{
			if (MB_ENABLED)
			{
				$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
			}
			elseif (ICONV_ENABLED)
			{
				$str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
			}
		}

		return $str;
	}

    /*
     * 删除所有在xml中可能导致问题的ASCII码字符
     */
	public function safe_ascii_for_xml($str)
	{
		return remove_invisible_characters($str, FALSE);
	}

	// --------------------------------------------------------------------

    // convert_to_utf8()函数将字符串转换为utf8编码，
    //首先如果mb_convert_encoding函数存在，
    //使用mb_convert_encoding函数转换，否则如果iconv函数存在，使用iconv转换；如果上面两个函数都不存在则不能转换返回false；如果转换完成返回转换后的字符串

	public function convert_to_utf8($str, $encoding)
	{
		if (MB_ENABLED)
		{
			return mb_convert_encoding($str, 'UTF-8', $encoding);
		}
		elseif (ICONV_ENABLED)
		{
			return @iconv($encoding, 'UTF-8', $str);
		}

		return FALSE;
	}

    // 检测是不是ASCII码
	public function is_ascii($str)
	{
		return (preg_match('/[^\x00-\x7F]/S', $str) === 0);
	}

}

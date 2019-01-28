<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class CI_Benchmark
 * 这个类可以标记点 计算它们之间的时间差
 * 内存消耗也可以
 */
class CI_Benchmark {


    // 用于存放所有标记点的数组
	public $marker = array();


	//记录当前的时间点
	public function mark($name)
	{
		$this->marker[$name] = microtime(TRUE);
	}

	// --------------------------------------------------------------------

    /*
     * 计算任意两点之间的运行时间
     *
     */
	public function elapsed_time($point1 = '', $point2 = '', $decimals = 4)
	{
	    // 如果第一个参数为空 ，即函数参数都为空的情况下 直接返回 4
		if ($point1 === '')
		{
			return '{elapsed_time}';
		}

		// 如果$point1 标记点不存在 则返回空
		if ( ! isset($this->marker[$point1]))
		{
			return '';
		}

		// 如果$point2标记点不存在  用当前时间生成一个$point2
		if ( ! isset($this->marker[$point2]))
		{
			$this->marker[$point2] = microtime(TRUE);
		}

		return number_format($this->marker[$point2] - $this->marker[$point1], $decimals);
	}

	// --------------------------------------------------------------------

      /**
       * 显示内存占用
       * 在视图文件中 使用下面这行代码来显示整个系统所占用的内存大小:
       * <?php echo $this->benchmark->memory_usage();?>,也可以1.81MB,
       * 这个方法只能在视图文件中使用，显示的结果代表整个应用所占用的内存大小。
       * 方法很简单，就是返回1.81MB
       *
       * 具体实现是在输出类Output.php中实现的
       * $memory = round(memory_get_usage() / 1024 / 1024, 2).'MB';
       * 然后用$memory替换了1.81MB
       * 这个核心函数是memory_get_usage()函数，
       * 函数原型int memory_get_usage ([ bool $real_usage = false ] )。如果$real_usage设置为 TRUE，获取系统分配的真实内存尺寸。
       * 如果未设置或者设置为 FALSE，将是 emalloc() 报告使用的内存量。
       * php5.2.1 后不需要在编译时使用 --enable-memory-limit选项就能够使用这个函数
       */
	public function memory_usage()
	{
		return '{memory_usage}';
	}

}

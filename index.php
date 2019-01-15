<?php

/**
 * @desc CI框架源码剖析 for index.php
 * @author banxuelai
 */


/**
 * 定义框架代码使用环境状态
 * 一般有development(开发) testing(测试) production(生产) 三个环境场景状态
 */
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');


/**
 * 针对不同的环境场景状态产生不同级别的错误报告
 */
//error_reporting()函数是php的内置函数，用来设置php的报错级别并返回当前级别
//函数语法：
//error_reporting(report_level) report_level参数是错误等级，一共有已下几种:
//值 常量 描述

//1 E_ERROR 致命的运行错误。错误无法恢复，暂停执行脚本
//2 E_WARNING 运行时警告(非致命性错误)。非致命的运行错误，脚本执行不会停止
//4 E_PARSE 编译时解析错误。解析错误只由分析器产生
//8 E_NOTICE 运行时提醒(这些经常是你代码中的bug引起的，也可能是有意的行为造成的。)
//16 E_CORE_ERROR PHP启动时初始化过程中的致命错误
//32 E_CORE_WARNING PHP启动时初始化过程中的警告(非致命性错)。
//64 E_COMPILE_ERROR 编译时致命性错。这就像由Zend脚本引擎生成了一个E_ERROR
///128 E_COMPILE_WARNING 编译时警告(非致命性错)。这就像由Zend脚本引擎生成了一个E_WARNING警告
//256 E_USER_ERROR 用户自定义的错误消息。这就像由使用PHP函数trigger_error（程序员设置E_ERROR）
//512 E_USER_WARNING 用户自定义的警告消息。这就像由使用PHP函数trigger_error（程序员设定的一个E_WARNING警告）
//1024 E_USER_NOTICE 用户自定义的提醒消息。这就像一个由使用PHP函数trigger_error（程序员一个E_NOTICE集）
//2048 E_STRICT 编码标准化警告。允许PHP建议如何修改代码以确保最佳的互操作性向前兼容性
//4096 E_RECOVERABLE_ERROR 开捕致命错误。这就像一个E_ERROR，但可以通过用户定义的处理捕获（又见set_error_handler（））
//8191 E_ALL 所有的错误和警告(不包括 E_STRICT) (E_STRICT will be part of E_ALL as of PHP 6.0)

switch (ENVIRONMENT)
{
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        if (version_compare(PHP_VERSION, '5.3', '>='))
        {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        }
        else
        {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
        }
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1); // EXIT_ERROR
}

/**
 * 定义系统目录名称
 * 里面存放的是CI框架的各种核心文件
 */
$system_path = 'system';

/**
 * 定义应用目录名称
 * 可以设置修改
 */
$application_folder = 'app';


/**
 * 定义视图文件存放目录
 */
$view_folder = '';

/**
 * STDIN(标准输入) STDOUT(标准输出) STDERR(标准错误流) 是PHP以CLI模式运行而定义的三个常量
 */
if (defined('STDIN'))
{
    chdir(dirname(__FILE__));
}


// 得到规范化的绝对路径名
if (($_temp = realpath($system_path)) !== FALSE)
{
    $system_path = $_temp.DIRECTORY_SEPARATOR;
}
else
{
    // Ensure there's a trailing slash
    $system_path = strtr(
            rtrim($system_path, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
        ).DIRECTORY_SEPARATOR;
}

// 如果$system_path所指向的文件目录不存在 则exit
if ( ! is_dir($system_path))
{
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
    exit(3); // EXIT_CONFIG
}

/**
 * 下面主要设置各种主要的常量
 */

//当前文件名称 也就是 "index.php" pathinfo返回文件路径的信息
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

//system文件的绝对路径
define('BASEPATH', $system_path);

//项目文件目录的绝对路径
//dirname()函数返回路径中的目录部分
define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

//system文件夹名称 “system”
//basename()函数返回路径中文件名部分
define('SYSDIR', basename(BASEPATH));

// 判断验证应用文件目录
if (is_dir($application_folder))
{
    if (($_temp = realpath($application_folder)) !== FALSE)
    {
        $application_folder = $_temp;
    }
    else
    {
        $application_folder = strtr(
            rtrim($application_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
        );
    }
}
elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
{
    $application_folder = BASEPATH.strtr(
            trim($application_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
        );
}
else
{
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
    exit(3); // EXIT_CONFIG
}

define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

//判断验证view视图文件目录
if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
{
    $view_folder = APPPATH.'views';
}
elseif (is_dir($view_folder))
{
    if (($_temp = realpath($view_folder)) !== FALSE)
    {
        $view_folder = $_temp;
    }
    else
    {
        $view_folder = strtr(
            rtrim($view_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
        );
    }
}
elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
{
    $view_folder = APPPATH.strtr(
            trim($view_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
        );
}
else
{
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
    exit(3); // EXIT_CONFIG
}

define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);


//最后就是加载CI框架的核心引导文件
require_once BASEPATH.'core/CodeIgniter.php';

<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Model {

    static public $serviceInstance = array();
    static public $dbConn = array();

    /**
	 * Class constructor
	 *
	 * @link	https://github.com/bcit-ci/CodeIgniter/issues/5332
	 * @return	void
	 */
	public function __construct() {
    }

	/**
	 * __get magic
	 *
	 * Allows models to access CI's loaded classes using the same
	 * syntax as controllers.
	 *
	 * @param	string	$key
	 */
	public function __get($key)
	{
		// Debugging note:
		//	If you're here because you're getting an error message
		//	saying 'Undefined Property: system/core/Model.php', it's
		//	most likely a typo in your model code.
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

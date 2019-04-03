<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Ip_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc IP转整型
     * @param $ipAddress
     * @return float|int
     * @author banxuelai@vcomic.com
     * @date 2019/4/3
     */
    public function ipToScore($ipAddress)
    {
        $ipArr = explode('.', $ipAddress);
        $scoreArr = array();
        $score = 0;
        foreach ($ipArr as $key => $ipItem)
        {
            if($key < 3) {
                $score += $ipItem * pow(256,intval(3 - $key));
            }
            else {
                $endArr = explode('/', $ipItem);
                for($i = $endArr[0]; $i <= $endArr[1]; $i ++)
                {
                    $scoreArr[] = $score + $i;
                }
            }
        }
        return $scoreArr;
    }

    /**
     * @desc 导入IP地址库
     * @param $fileName
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/4/3
     */
    public function importIpsToRedis($fileName)
    {
        $fileName = trim($fileName);

        if(! is_file($fileName) || ! file_exists($fileName)) {
            return array('code' => 0, 'message' => '导入文件不存在');
        }

        # 开始读取csv文件数据
        $cvsFile = fopen($fileName, 'r');
        $i = 0; # 记录cvs的行
        while($fileData = fgetcsv($cvsFile))
        {
            $i++;
            if($i == 1) {
                continue;
            }
            if(! empty($fileData[0]) && ! empty($fileData[1])) {
                $ipAddress = $fileData[0];
                $cityId = $fileData[1];

                $ipArr = $this->ipToScore($ipAddress);
                foreach($ipArr as $ip)
                {
                    # 写入有序集合 有序集合 zset  城市ID为member  IP整型为score
                    echo  "ip2cityid:".'---city_id:'.$cityId.'----ip:'.$ip.PHP_EOL;
                    $this->redisInit()->master()->zadd("ip2cityid:", $cityId, $ip);
                }
            }
        }

        return array('code' => 1, 'message' => 'ok');
    }

    /**
     * @desc 导入城市信息库
     * @param $fileName
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/4/3
     */
    public function importCitiesToRedis($fileName)
    {
        $fileName = trim($fileName);

        if(! is_file($fileName) || ! file_exists($fileName)) {
            return array('code' => 0, 'message' => '导入文件不存在');
        }

        # 开始读取csv文件数据
        $cvsFile = fopen($fileName, 'r');
        $i = 0; # 记录cvs的行
        while($fileData = fgetcsv($cvsFile))
        {
            $i++;
            if($i == 1) {
                continue;
            }
            if(! empty($fileData[0]) && ! empty($fileData[1]))
            {
                $cityId = $fileData[0];
                $continentName = $fileData[3]; # 洲
                $countryName = $fileData[5]; # 国家
                $subdivision_1_name = $fileData[7]; # 省1
                $subdivision_2_name = $fileData[9]; # 省2
                $cityName = $fileData[11]; # 城市

                $city = $continentName.$countryName.$subdivision_1_name.$subdivision_2_name.$cityName;
                echo  "cityid2city:".'---city_id'.$cityId.'----city'.$city.PHP_EOL;
                # 入redis hash
                $this->redisInit()->master()->hset("cityid2city:", $cityId, $city);
            }
        }
        return array('code' => 1, 'message' => 'ok');
    }
}
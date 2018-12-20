<?php

class CI_Elasticsearch
{
    public $server = '';

    public $index = '';

    public $esCfg = array();

    public function __construct()
    {
        $this->esCfg = gc("elasticsearch");
    }

    /**
     * @desc 主服务
     * @param $indexName
     * @return $this  * @throws Exception
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function masterEs($indexName)
    {
        $esConnect = '';
        foreach ($this->esCfg as $key => $value)
        {
            foreach($value['index'] as $index){
                if (preg_match("/{$index}/i", $indexName)) {
                    $esConnect = $key;
                    break 2;
                }
            }
        }

        if (empty($esConnect)) {
            throw new Exception('Elascticsearch master: indexName is not exist');
        }

        $masterList = empty($this->esCfg[$esConnect]["master"]) ? array() : $this->esCfg[$esConnect]["master"];

        if (empty($masterList)) {
            throw new Exception("Elascticsearch master: config is empty");
        }

        while (true)
        {
            // 无可选host 跳出
            if (count($masterList) == 0) {
                break;
            }

            $randomKey = array_rand($masterList, 1);
            $this->server = $masterList[$randomKey];

            // ping
            $res = $this->ping();

            if (! empty($res["name"]) || ! empty($res["cluster_name"])) {
                // 连接成功 跳出
                break;
            } else {
                // 重置server, 并从可选地址中移除
                $this->server = "";
                unset($masterList[$randomKey]);
            }

        }

        if (empty($this->server)) {
            throw new Exception('Elascticsearch master: service is not available!!!');
        }

        $this->index = $indexName;
        return $this;
    }

    /**
     * @desc 从服务
     * @param $indexName
     * @return $this  * @throws Exception
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function slaveEs($indexName)
    {
        $esConnect = '';
        foreach ($this->esCfg as $key => $value)
        {
            foreach($value['index'] as $index){
                if (preg_match("/{$index}/i", $indexName)) {
                    $esConnect = $key;
                    break 2;
                }
            }
        }

        if (empty($esConnect)) {
            throw new Exception('Elascticsearch slave: indexName is not exist');
        }

        $slaveList = empty($this->esCfg[$esConnect]["slave"]) ? array() : $this->esCfg[$esConnect]["slave"];

        if (empty($slaveList)) {
            throw new Exception("Elascticsearch slave: config is empty");
        }

        while (true)
        {
            // 无可选host 跳出
            if (count($slaveList) == 0) {
                break;
            }

            $randomKey = array_rand($slaveList, 1);
            $this->server = $slaveList[$randomKey];

            // ping
            $res = $this->ping();

            if (! empty($res["name"]) || ! empty($res["cluster_name"])) {
                // 连接成功 跳出
                break;
            } else {
                // 重置server, 并从可选地址中移除
                $this->server = "";
                unset($slaveList[$randomKey]);
            }

        }

        if (empty($this->server)) {
            throw new Exception('Elascticsearch slave: service is not available!!!');
        }

        $this->index = $indexName;
        return $this;
    }

    /**
     * @desc ping
     * @return mixed
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function ping()
    {
        return $this->_esRequest("", "", "", 'GET', "");
    }

    /**
     * @desc 添加
     * @param $type
     * @param $id
     * @param $data
     * @return mixed
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function add($type, $id, $data)
    {
        $type = ! empty($type) ? trim($type) : $this->index;
        return $this->_esRequest($this->index, $type, $id, 'PUT', $data);
    }

    /**
     * @desc 查询
     * @param $type
     * @param $query
     * @return mixed
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function advancedquery($type, $query)
    {
        $type = ! empty($type) ? trim($type) : $this->index;
        return $this->_esRequest($this->index, $type, "_search", 'POST', $query);
    }

    /**
     * @desc 计算数量
     * @param $type
     * @param $query
     * @return mixed
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function queryCount($type, $query)
    {
        $type = ! empty($type) ? trim($type) : $this->index;
        return $this->_esRequest($this->index, $type, "_count", 'POST', $query);
    }

    /**
     * @desc 批量插入
     * @param $data
     * @return mixed
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function esBluk($data)
    {
        return $this->_esRequest('', '', '_bulk', 'POST', $data);
    }

    /**
     * @desc 删除
     * @param $index
     * @param $type
     * @param $id
     * @return mixed
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function delete($index, $type, $id)
    {
        return $this->_esRequest($index, $type, $id, 'DELETE', array());
    }

    /**
     * @desc 获取服务器所有索引
     * @return mixed
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    public function catIndices()
    {
        return $this->_esRequest('', '', '_cat/indices', 'GET', array());
    }

    /**
     * @desc es请求
     * @param $index
     * @param $type
     * @param $path
     * @param string $method
     * @param string $data
     * @return mixed
     * @author banxuelai@vcomic.com
     * @date 2018/12/20
     */
    private function _esRequest($index, $type, $path, $method = 'GET', $data = "")
    {
        $index = ! empty($index) ? trim($index) : "";
        $type  = ! empty($type) ? trim($type) : "";
        $path  = ! empty($path) ? trim($path) : "";
        $data  = ! empty($data) ? $data : array();

        if (is_array($data)) {
            $data = json_encode($data);
        }

        $url = $this->server;

        if (! empty($index)) {
            $url .= "/" . $index;
        }

        if (! empty($type)) {
            $url .= "/" . $type;
        }

        if (! empty($path)) {
            $url .= "/" . $path;
        }

        $headers = array('Accept: application/json', 'Content-Type: application/json');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        switch ($method)
        {
            case 'GET' :
                break;

            case 'POST' :
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;

            case 'PUT' :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;

            case 'DELETE' :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
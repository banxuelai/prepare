<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Es_Prepare_service extends Servicelib
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Elasticsearch');
    }
    private $_projectFields = array(
        'project_id','project_title','project_fees','project_summary','project_status','project_source','create_time',
    );
    /**
     * @desc 同步数据
     * @author banxuelai@vcomic.com
     * @date 2019/3/21
     */
    public function synDataToEs()
    {
        $fileds = "project_id,project_title,project_fees,project_summary,project_status,project_source,create_time";
        $projectList = $this->master()->select($fileds)->from(self::Table_Project_Info)
            ->where('project_status', 1)->get()->result_array();

        if (empty($projectList)) {
            echo date('Y-m-d H:i:s') . "||message: projectList empty\r\n";
        }

        foreach ($projectList as $value)
        {
            # 写入es
            $res = $this->addEs($value);
            if (empty($res['code'])) {
                echo date('Y-m-d H:i:s') . "|synDataToEs|project_id:{$value['project_id']}|message:{$res['message']}\r\n";
            }else{
                echo date('Y-m-d H:i:s') . "|synDataToEs|project_id:{$value['project_id']}|message:{$res["message"]}\r\n";
            }
        }
    }

    /**
     * @desc 写入es
     * @param $row
     * @return array
     * @author banxuelai@vcomic.com
     * @date 2019/3/21
     */
    public function addEs($projectRow)
    {
        $projectRow = is_array($projectRow) ? $projectRow : array();
        if(empty($projectRow))
        {
            return array('code' => 0, 'message' => 'project_row empty');
        }

        if(empty($projectRow['project_id']))
        {
            return array('code' => 0, 'message' => 'project_id empty');
        }

        $documentProject = array();

        # 构造数据
        foreach ($this->_projectFields as $value)
        {
            $documentProject[$value] = isset($projectRow[$value]) ? $projectRow[$value] : '';
        }

        # 写入
        $esProjectIndexName = 'project_20190321';
        $res = $this->elasticsearch->masterEs($esProjectIndexName)->add($esProjectIndexName, $projectRow['project_id'], $documentProject);

        if(isset($res['error'])) {
            $resJson = json_encode($res);
            return array('code' => 0, 'message' => "index : {$esProjectIndexName} error : {$resJson}");
        }

        return array('code' => 1, 'message' => 'comic add ok');
    }
}
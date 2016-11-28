<?php

/**
 * 标准返回
 * api的标准返回格式
 * @author Mr.Nobody
 */
class StdReturn
{

    function __get($key)
    {
        $CI = & get_instance();
        return $CI->$key;
    }

    public function ok($data = array())
    {
        $decorate = array(
            'status' => 0,
            'msg' => 'SUCCESS',
            'data' => $data,
        );          
        $decorate = json_encode($decorate);    
        
        //如需要，进行jsonp修饰    
        $this->_jsonpDecorate($decorate);  

        header('Content-Type:application/json');
        $this->output->set_output($decorate);
        return $decorate;
    }

    public function failed($ret, $data = array(), $append = '', $level = 'notice')
    {
        $this->lang->load('myerror');
        $msg = '';        
        if (isset($this->lang->language['myerror'][$ret])) {
            $msg = $this->lang->language['myerror'][$ret];
        }
        
        $decorate = array(
            'status' => $ret,
            'msg' => $msg,
            'data' => NULL
        );
        if ($append) {
            if ($this->config->item('debug')) {
                $decorate['msg'] .= '(' . $append . ')';
            }
        }
        if ($data) {
            $decorate['data'] = $data;
        }
        
        $decorate = json_encode($decorate);        
        
        //如需要，进行jsonp修饰
        $this->_jsonpDecorate($decorate);   

        //记录失败日志
        $this->_failLog($decorate, $append);

        header('Content-Type:application/json');
        $this->output->set_output($decorate);
        return $decorate;
    }
    
    private function _jsonpDecorate(&$jsonData) 
    {
        $jsonp = $this->input->get('jsonpCallback', TRUE);
        if ($jsonp) {
            $jsonData = $jsonp . '(' . $jsonData. ')';
        }
    }
    
    private function _failLog($decorate, $append) 
    {

        $api = $this->uri->uri_string;
        
        $params = array();
        if ($this->input->get()) {
            $params += (array)$this->input->get(NULL, TRUE);
        }
        if ($this->input->post()) {
            $params += (array)$this->input->post(NULL, TRUE);
        }
        
        // 记录failed日志
        $this->load->helper('common');
        $content = compact('api', 'params', 'decorate', 'append');
        write_notice(json_encode($content), FALSE, 'failed');

    }
    
    
}

/* End of file stdreturn.php */
/* Location: ./application/libaries/stdreturn.php */
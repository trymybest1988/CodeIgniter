<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 参数检查 钩子
 * @author Mr.Nobody
 */
class Param
{
    
    function __get($key)
    {
        $CI = & get_instance();
        return $CI->$key;
    }

    public function validate()
    {
        $this->config->load('param/' . lcfirst($this->router->class));
        $paramConf = $this->config->item('param');
        $interface = $this->uri->uri_string;      
          
        if (isset($paramConf[$interface])) {
                        
            $paramConf[$interface]['method'] = isset($paramConf[$interface]['method']) ? $paramConf[$interface]['method'] : '';  
            $this->iniParams($paramConf[$interface]['method']);
            
            $result = $this->verification($paramConf[$interface]['rules']);

            // 若检查失败，返回错误，并退出
            if (! $result) {
                throw new exceptions\ParamIllegalException(validation_errors());
            }
            
        } else {
            $this->iniParams();
        }
        
    }

    protected function verification($config)
    {
        // 检查
        $this->load->library('Form_validation');
        $this->form_validation->set_data($this->params);
        $this->form_validation->set_rules($config);
        $result = $this->form_validation->run();

        // 回设参数
        $this->params = $this->form_validation->validation_data;

        return $result;
    }
    
    protected function iniParams($method = '')
    {
        $params = array();
        
        if (! $method) {
            if ($this->input->get()) {
                $params += (array)$this->input->get(NULL, TRUE);
            }
            if ($this->input->post()) {
                $params += (array)$this->input->post(NULL, TRUE);
            }
        } elseif ($method == HTTP_REQUEST_METHOD_GET) {
            $params = (array)$this->input->get(NULL, TRUE);
        } elseif ($method == HTTP_REQUEST_METHOD_POST){
            $params = (array)$this->input->post(NULL, TRUE);
        }
        
        // 回设controller的参数数组
        $this->params = $params;
    }

}

/* End of file Param.php */
/* Location: ./application/hooks/Param.php */
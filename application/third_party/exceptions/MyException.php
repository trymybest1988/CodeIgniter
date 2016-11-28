<?php 
namespace exceptions;

/**
 * 自定义异常基类
 * @author Mr.Nobody
 *
 */
class MyException extends \Exception
{

    function __get($key)
    {
        $CI = & get_instance();
        return $CI->$key;
    }
    
    /**
     * 
     * @param int $code
     * @param array $logInfo
     */
    public function logAndExit($code, $logInfo, $level = 'WARNING')
    {

        $this->load->helper('common');
        log_message($level, json_encode($logInfo));
        
        $this->load->library('StdReturn', '', 'StdReturn');
        $return = $this->StdReturn->failed($code);  
        exit($return);
    }
    
    public function handleException() 
    {

        $interface = $this->uri->uri_string;
        $params = (array)$this->input->get() + (array)$this->input->post();
        
        $logInfo = array(
            'api' => $interface,
            'params' => $params,
            'exceptionInfo' =>$this->message
        );
        
        $this->logAndExit(- 1000, $logInfo);
        
    }
    
}



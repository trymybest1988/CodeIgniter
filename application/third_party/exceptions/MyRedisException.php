<?php 
namespace exceptions;

/**
 * redis异常
 * @author Mr.Nobody
 *
 */
class MyRedisException extends MyException
{
    
    public function handleException() 
    {

        $interface = $this->uri->uri_string;
        $params = (array)$this->input->get() + (array)$this->input->post();
        
        $errorInfo = array(
            'api' => $interface,
            'params' => $params,
            'exceptionInfo' =>$this->message
        );
        
        $this->logAndExit(- 1002, $errorInfo, 'FATAL');
        
    }
    
    
}



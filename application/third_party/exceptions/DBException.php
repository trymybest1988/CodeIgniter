<?php 
namespace exceptions;

/**
 * 数据库异常
 * @author Mr.Nobody
 *
 */
class DBException extends MyException
{
    
    public function handleException() 
    {

        $interface = $this->uri->uri_string;
        $params = (array)$this->input->get() + (array)$this->input->post();
        
        $logInfo = array(
            'api' => $interface,
            'params' => $params,
            'exceptionInfo' =>$this->message
        );
        
        $this->logAndExit(- 1001, $logInfo, 'FATAL');
        
    }
    
    
}



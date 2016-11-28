<?php
namespace exceptions;

/**
 * 参数不合法异常
 * @author Mr.Nobody
 *
 */
class ParamIllegalException extends MyException
{
    public function handleException()
    {
    
        $interface = $this->uri->uri_string;
        $params = (array)$this->input->get() + (array)$this->input->post();
    
        $search = array("\n", "\r", '</p>', '<p>', '<strong>', '</strong>');
        $this->message = str_replace($search, ' ', $this->message);
    
        $errorInfo = array(
            'api' => $interface,
            'params' => $params,
            'exceptionInfo' =>$this->message ? $this->message : '参数验证失败'
        );
    
        $this->logAndExit(- 1101, $errorInfo, 'WARNING');
    
    }
    
    
}



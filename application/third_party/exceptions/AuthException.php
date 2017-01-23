<?php 
namespace exceptions;

/**
 * 未登陆不合法异常
 * @author Mr.Nobody
 *
 */
class AuthException extends MyException
{
    
    public function handleException() 
    {
        $this->logAndExit(- 1100, 'NOTICE');        
    }
    
    
}



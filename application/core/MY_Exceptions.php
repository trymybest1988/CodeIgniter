<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Exceptions extends CI_Exceptions
{

    public function show_exception($exception)
    {
        $exceptionClass = get_class($exception);
        
        if (class_exists($exceptionClass) && method_exists($exception, 'handleException')) {
            $exception->handleException();
        } else {
            set_status_header(500);
            $myException = new exceptions\MyException();
            $myException->logAndExit(- 1000, json_encode($exception));
        }
    }
}

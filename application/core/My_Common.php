<?php

if ( ! function_exists('_shutdown_handler'))
{
    function _shutdown_handler()
    {
        log_finish();
        $last_error = error_get_last();
        if (isset($last_error) &&
            ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)))
        {
            _error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
        }
    }
}

if ( ! function_exists('log_finish'))
{
    function log_finish()
    {
        $log = load_class('Log');
    
        global $BM, $class, $method;
    
        $total_execution_time = $BM->elapsed_time('total_execution_time_start');
    
        $loading_time = $BM->elapsed_time('loading_time:_base_classes_start', 'loading_time:_base_classes_end');
    
        $controller_execution_time = $BM->elapsed_time('controller_execution_time_( ' . $class . ' / ' . $method . ' )_start', 'controller_execution_time_( ' . $class . ' / ' . $method . ' )_end');
    
        $content = vsprintf("[mark=request_out][proc_time=%f][time_total=%f][time_load_base=%f][time_ac_exe=%f (s)][memory_use=%f][memory_peak=%f (MB)]", array(
            $total_execution_time,
            $total_execution_time,
            $loading_time,
            $controller_execution_time,
            memory_get_usage(true) /1024.0 / 1024.0,
            memory_get_peak_usage(true) / 1024.0 / 1024.0
        ));
        $log->notice(array($content));
        $log->flush();
    }
}

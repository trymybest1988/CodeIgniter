<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

if (! function_exists('get_args_by_keys')) {

    function get_args_by_keys($args, $keys)
    {
        extract($args);
        return compact($keys);
    }
}

/**
 * 输出文件日志 - notice
 */
if (! function_exists('write_notice')) {

    function write_notice($content)
    {
        if (! $content) {
            return false;
        }
        
        $_log = & load_class('Log');
        if ($_log) {
            $_log->write_log('notice', $content);
        }
    }
}

/**
 * 输出文件日志 - warning
 */
if (! function_exists('write_warning')) {

    function write_warning($content)
    {
        if (! $content) {
            return false;
        }
        
        $_log = & load_class('Log');
        if ($_log) {
            $_log->write_log('warning', $content);
        }
    }
}

/**
 * 输出文件日志 - fatal
 */
if (! function_exists('write_fatal')) {

    function write_fatal($content)
    {
        if (! $content) {
            return false;
        }
        
        $_log = & load_class('Log');
        if ($_log) {
            $_log->write_log('fatal', $content);
        }
    }
}





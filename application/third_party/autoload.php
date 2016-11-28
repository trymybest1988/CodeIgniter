<?php

// 支持匹配规则：a_b 或  a/b 或 a\b
function common_autoload($class){
    
    if (function_exists('__autoload')) {
        //    Register any existing autoloader function with SPL, so we don't get any clashes
        spl_autoload_register('__autoload');
    }
    $file = preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')).'.php';

    // 过滤CI类、CI覆盖类
    if (strpos($file, 'CI') === 0 || strpos($file, 'MY') === 0) {
        return ;
    }
    
    // 判断文件是否存在
    if (file_exists(APPPATH . 'third_party/' . $file)) {
        require $file;
    }

}
spl_autoload_register('common_autoload');


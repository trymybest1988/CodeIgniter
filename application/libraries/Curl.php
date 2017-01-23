<?php

class Curl
{

    /**
     * 提交GET请求，curl方法
     * 
     * @param string $url
     *            请求url地址
     * @param mixed $data
     *            GET数据,数组或类似id=1&k1=v1
     * @param array $header
     *            头信息
     * @param int $timeout
     *            超时时间
     * @param int $port
     *            端口号
     * @return array 请求结果,
     *         如果出错,返回结果为array('error'=>'','result'=>''),
     *         未出错，返回结果为array('result'=>''),
     */
    public function get($url, $data = array(), $header = array(), $connect_timeout = 5000, $excute_timeout = 5000)
    {
        $ch = curl_init();
        if (! empty($data)) {
            $data = is_array($data) ? http_build_query($data) : $data;
            $url .= (strpos($url, '?') ? '&' : "?") . $data;
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $excute_timeout);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // curl 1000ms bug http://www.laruence.com/2014/01/21/2939.html
        if ($connect_timeout <= 1000 || $excute_timeout <= 1000) {
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 提交POST请求，curl方法
     * 
     * @param string $url
     *            请求url地址
     * @param mixed $data
     *            POST数据,数组或类似id=1&k1=v1
     * @param array $header
     *            头信息
     * @param int $timeout
     *            超时时间
     * @param int $port
     *            端口号
     * @return string 请求结果,
     *         如果出错,返回结果为array('error'=>'','result'=>''),
     *         未出错，返回结果为array('result'=>''),
     */
    public function post($url, $data = array(), $header = array(), $connect_timeout = 5000, $excute_timeout = 5000)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $excute_timeout);
        ! empty($header) && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        // curl 1000ms bug http://www.laruence.com/2014/01/21/2939.html
        if ($connect_timeout <= 1000 || $excute_timeout <= 1000) {
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        }
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }
}

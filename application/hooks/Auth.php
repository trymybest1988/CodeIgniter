<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 参数检查类
 * @author Mr.Nobody
 *
 */
class Auth
{
    
    function __get($key)
    {
        $CI = & get_instance();
        return $CI->$key;
    }
    
    function __set($key, $value)
    {
        // 回设controller的属性
        $CI = & get_instance();
        $CI->$key = $value;
    }
    
    public function auth()
    {
        $this->config->load('auth');
        $auths = config_item('auth');

        $interface = $this->uri->uri_string;
        if (! isset($auths['whiteList'][$interface])) {
            if (! isset($this->params['token'])) {
                throw new exceptions\ParamIllegalException('token is null');
            }
            $this->load->model('TokenModel');
            $datas = $this->TokenModel->getTokenDatas($this->params['token']);
            if (empty($datas)) {
                throw new exceptions\AuthException();
            }
            $this->token = $datas;
        }
    }


}

/* End of file Auth.php */
/* Location: ./application/hooks/Auth.php */
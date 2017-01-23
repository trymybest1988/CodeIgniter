<?php

/**
 * 表缓存类
 * 基于redis的hash结构缓存表数据。
 * @author Mr.Nobody
 *
 */
class RCache
{
    
    public function __construct()
    {
        
    }
    
    /**
     * 当前配置
     * @var array
     */
    protected $conf = array();
    
    /**
     * redis连接对象
     * @var redisconn
     */
    protected $redis = NULL;
    
    /**
     * 数据库连接对象
     * @var dbconn
     */
    protected $db_using = NULL;
    
    /**
     * redis 键
     * @var string
     */
    protected $key = NULL;    
    
    protected $func_get = '';
    
    protected $func_set = '';

    protected function init($confId, $primaryKV)
    {        
        $this->config->load('rcache');
        $conf = $this->config->item('rcache'); 
        $conf = $conf[$confId];
        
        if (empty($this->conf) || $this->conf['confId'] != $confId) {
            
            $this->load->library('RedisPool', '', 'RedisPool');            
            $this->conf = $conf;
            $this->conf['confId'] = $confId;
            $this->redis = $this->RedisPool->getConnInstance($conf['redis']);
        }     

        $this->getRedisKey($primaryKV);
        
        switch ($this->conf['type']) {
            case REDIS_CACHE_TYPE_HASH :
                $this->func_get = 'hgetall';
                $this->func_set = 'hmset';
                break;
            case REDIS_CACHE_TYPE_STRING:
                $this->func_get = 'get';
                $this->func_set = 'set';
                break;
            default:
                $this->stdreturn->failed(-1103);
                exit();
        }
        
    }

    /**
     * 初始化db连接
     */
    protected function loadDB()
    {
        $this->db_using = $this->load->database($this->conf['mysql'], TRUE);
    }
    
    /**
     * 构造缓存的键
     * 
     * @param array $primaryKV
     * @return string
     */
    protected function getRedisKey($primaryKV)
    {
        $this->key = $this->conf['prefix'] ? $this->conf['prefix'] : '';
        
        $keys = explode(':', $this->conf['key']);
        foreach ($keys as $key) {
            $this->key .= ':' . $primaryKV[$key];
        }
    }
    
    protected function setExpire()
    {
        if (isset($this->conf['expire'])) {
            $this->redis->expire($this->key, $this->conf['expire']);
        }
    }
    
    /**
     * 强制同步redis
     */
    protected function sync($primaryKV)
    {
        $rs = $this->db_using->get_where($this->conf['table'], $primaryKV)->row_array();
    
        if ($rs) {
            call_user_func_array(array($this->redis, 'hmset'),
            array($this->key, $rs));
        }
    
        return $rs;
    }
    
    protected function getRowFromDB($primaryKV)
    {
        $this->loadDB();
        
        $rs = $this->db_using->get_where($this->conf['table'], $primaryKV)->row_array();
        
        if (FALSE === $rs) {
            throw new DBException('rcache getRowFromDB error');
        } 
        
        return $rs;
    }
    
    protected function updateRowFromDB($primaryKV, $data)
    {
        $this->loadDB();
        
        $this->db_using->where($primaryKV);
        $rs = $this->db_using->update($this->conf['table'], $data);
        
        if (FALSE === $rs) {
            throw new DBException('rcache getRowFromDB error');
        } 
    }

    protected function insertRowFromDB($data)
    {
        $this->loadDB();
        
        $rs = $this->db_using->insert($this->conf['table'], $data);
    
        if (FALSE === $rs) {
            throw new DBException('rcache insertRowFromDB error');
        }
        
        return $rs;
    }
    
    protected function deleteRowFromDB($primaryKV)
    {
        $this->loadDB();
        
        $rs = $this->db_using->delete($this->conf['table'], $primaryKV);
    
        if (FALSE === $rs) {
            throw new DBException('rcache deleteRowFromDB error');
        }
    }

    public function __get($key)
    {
        $CI = & get_instance();
        return $CI->$key;
    }
    
    /**
     * 取数据
     * 
     * @param string $confId
     * @param array $primaryKV
     * @return array
     */
    public function getDatas($confId, $primaryKV)
    {
        $this->init($confId, $primaryKV);
        
        $rs = call_user_func_array(array($this->redis, $this->func_get),
                array($this->key));
        
        if ($rs) {
            if (REDIS_CACHE_TYPE_STRING == $this->conf['type']) {
                $rs = json_decode($rs, TRUE);
            }
        } elseif ($this->conf['sync']) {
            $rs = $this->getRowFromDB($primaryKV);
            if ($rs) {
                if (REDIS_CACHE_TYPE_STRING == $this->conf['type']) {
                    $rs = json_encode($rs);
                }
                call_user_func_array(array($this->redis, $this->func_set),
                    array($this->key, $rs));
            }
        }
        
        if ($rs) {
            $this->setExpire();
        }
        
        return $rs;
    
    }
    
    /**
     * 添加数据
     *
     * @param string $confId
     * @param array $primaryKV
     * @param array $data
     * @return array
     */
    public function insertDatas($confId, $primaryKV, $data)
    {
        $this->init($confId, $primaryKV);
        
        $this->insertRowFromDB($data);
        
        call_user_func_array(array($this->redis, $this->func_set),
            array($this->key, $data));
        
        $this->setExpire();    
    }
    
    /**
     * 编辑数据
     *
     * @param string $confId
     * @param array $primaryKV
     * @param array $data
     * @return array
     */
    public function updateDatas($confId, $primaryKV, $data)
    {
        $this->init($confId, $primaryKV);
        
        $this->updateRowFromDB($primaryKV, $data);
        
        call_user_func_array(array($this->redis, $this->func_set),
            array($this->key, $data));
        
        $this->setExpire();
    }
    
    /**
     * 删除数据
     *
     * @param string $confId
     * @param array $primaryKV
     * @return array
     */
    public function deleteDatas($confId, $primaryKV)
    {
        $this->init($confId, $primaryKV);
                
        $this->deleteRowFromDB($primaryKV);
    
        call_user_func_array(array($this->redis, 'del'), 
            array($this->key)); 
    }
    
    /**
     * 状态删除数据
     * $data 中必须有“状态”字段键值对
     * @param string $confId
     * @param array $primaryKV
     * @param array $data
     * @return array
     */
    public function statusDeleteDatas($confId, $primaryKV, $data)
    {
        $this->init($confId, $primaryKV);
                
        $this->updateDatas($confId, $primaryKV, $data);
    
        call_user_func_array(array($this->redis, 'del'), 
            array($this->key)); 
    }


}

/* End of file RCache.php */
/* Location: ./application/libaries/RCache.php */
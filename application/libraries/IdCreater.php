<?php
/**
 * id 构造类
 * @author Mr.Nobody
 *
 */
class IdCreater
{

    /**
     * id生成类型：使用时间戳随机生成
     * @var int
     */
    const ID_CREATER_TYPE_TIME = 0;
    
    /**
     * id生成类型：自增id
     * @var int
     */
    const ID_CREATER_TYPE_INCREAMENT = 1;
    
    /**
     * 锁的key前缀
     * @var string
     */
    const LOCK_PREFIX = 'ID_CREATER_LOCK';

    /**
     * 锁的最长持有时间(s)
     * @var string
     */
    const LOCK_EXPIRE_TIME = 1;
    
    /**
     * 当前id的key前缀
     * @var string
     */
    const CURRENT_ID_PREFIX = 'ID_CREATER_CURRENT_ID';
    
    /**
     * id生成的配置
     * @var string
     */
    protected $conf = array();
    
    protected $redis = NULL;
    
    function __get($key)
    {
        $CI = & get_instance();
        return $CI->$key;
    }

    /**
     * 生成id
     * @param string $confId
     */
    public function createId($confId)
    {
        if (! $this->conf) {
            $this->_init($confId);
        }
        if ($this->conf['type'] == self::ID_CREATER_TYPE_INCREAMENT) {
            return $this->_createIdIncrement();
        } elseif ($this->conf['type'] == self::ID_CREATER_TYPE_TIME) {
            return $this->_createIdTime();
        }
    }
    
    /**
     * 初始化
     * @param string $confId
     */
    private function _init($confId)
    {   
        $conf = $this->config->load('id_creater');
        $conf = $this->config->item('id_creater');     
        if (! isset($conf[$confId])) {
            $this->stdreturn->failed(-1003, array(), 'id not found in id_creater config');
        }

        $this->conf = $conf[$confId];
        $this->conf['confId'] = $confId;
        
        if ($this->conf['type'] == self::ID_CREATER_TYPE_INCREAMENT) {
            $this->load->library('RedisPool', '', 'RedisPool');
            $this->redis = $this->RedisPool->getConnInstance($this->conf['redis']);
        }

    }
    
    /**
     * 递增生成id
     * int 型id
     * @return string
     */
    private function _createIdIncrement()
    {
        // 获取锁
        $lock = $this->_getLock();
        if ($lock) {
            $currentIdKey = self::CURRENT_ID_PREFIX . '_' . $this->conf['confId'];
            $currentId = $this->redis->get($currentIdKey);
            $currentId = $currentId ? ($currentId + $this->conf['increament_once']) 
                : $this->conf['increament_base'];
            
            $this->redis->set($currentIdKey, $currentId);

            // 释放锁
            $this->_dropLock();
            
            return $currentId;
        } 
        
        // 获取锁失败
        $this->stdreturn->failed(-1103, array(), 'id not found in id_creater config');

    }
    
    /**
     * 按时间顺序生成id
     * (22位数字)
     * @return string
     */
    private function _createIdTime()
    {
        // 取毫秒时间，13位
        $time=microtime();
        $timeval = substr($time, 11) . substr($time, 2, 3);
        
        // 对后缀crc32格式化，再取前6位
        $suffix = crc32($this->conf['confId']);
        $suffix = sprintf("%u", $suffix);
        $suffix = substr($suffix, 0, 6);
        
        return $timeval . mt_rand(100, 999) . $suffix;
    }
    
    /**
     * 获取锁
     * @return boolean
     */
    private function _getLock()
    {
        // 静态变量用来记录尝试次数
        static $attempt = 1;

        // 设置锁值
        $keyLock = self::LOCK_PREFIX . "_" . $this->conf['confId'];
        $lock = $this->redis->setnx($keyLock, 1);
        
        // 若设置成功，则认为获得锁
        if ($lock) {
            // 设置锁持有的最长时间
            $this->redis->expire($keyLock, self::LOCK_EXPIRE_TIME);
            return TRUE;
        } elseif ($attempt < 11) {
            // 微秒数延迟执行
            usleep(mt_rand(100, 8000));
            $attempt ++;
            return $this->_getLock();
        }
        
        // 获取失败
        return FALSE;
    }
    
    /**
     * 释放锁
     * @return boolean
     */
    private function _dropLock()
    {
        $keyLock = self::LOCK_PREFIX . "_" . $this->conf['confId'];
        return $this->redis->del($keyLock);
    } 
    
}


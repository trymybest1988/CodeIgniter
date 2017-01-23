<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * type: 0 时间顺序的唯一id；1 自增id
 */

if ( ! defined('ID_CREATER_TYPE_TIME')) define('ID_CREATER_TYPE_TIME', 0);
if ( ! defined('ID_CREATER_TYPE_INCREAMENT')) define('ID_CREATER_TYPE_INCREAMENT', 1);

// tradeId
$config['id_creater']['plan']['type'] = ID_CREATER_TYPE_TIME;
$config['id_creater']['plan']['redis'] = 'default';

// userId
$config['id_creater']['user']['type'] = ID_CREATER_TYPE_INCREAMENT;
$config['id_creater']['user']['redis'] = 'default';
$config['id_creater']['user']['increament_base'] = 1000;
$config['id_creater']['user']['increament_once'] = 1;


/* End of file database.php */
/* Location: ./application/config/redis.php */
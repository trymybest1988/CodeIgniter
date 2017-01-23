<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| REDIS CACHE SETTINGS
| -------------------------------------------------------------------
*/

if ( ! defined('REDIS_CACHE_TYPE_HASH')) define('REDIS_CACHE_TYPE_HASH', 'hash');
if ( ! defined('REDIS_CACHE_TYPE_STRING')) define('REDIS_CACHE_TYPE_STRING', 'string');

$config['rcache']['user']['prefix'] = 'rcache:user';
$config['rcache']['user']['key'] = 'id';
$config['rcache']['user']['redis'] = 'default';
$config['rcache']['user']['mysql'] = 'default';
$config['rcache']['user']['table'] = 'user';
$config['rcache']['user']['type'] = REDIS_CACHE_TYPE_HASH;
$config['rcache']['user']['expire'] = 3600 * 24 * 7;
$config['rcache']['user']['sync'] = TRUE;

$config['rcache']['plan']['prefix'] = 'rcache:plan';
$config['rcache']['plan']['key'] = 'id';
$config['rcache']['plan']['redis'] = 'default';
$config['rcache']['plan']['mysql'] = 'default';
$config['rcache']['plan']['table'] = 'plan';
$config['rcache']['plan']['type'] = REDIS_CACHE_TYPE_HASH;
$config['rcache']['plan']['expire'] = 3600 * 24 * 7;
$config['rcache']['plan']['sync'] = TRUE;


/* End of file rcache.php */
/* Location: ./application/config/rcache.php */
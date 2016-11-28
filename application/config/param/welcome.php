<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * welcome接口规则
 * @author Mr.Nobody
 *
 */

$config['param']['welcome/index']['method'] = HTTP_REQUEST_METHOD_GET;
$config['param']['welcome/index']['rules'] = array(
    array(
        'field' => 'hello',
        'label' => 'hello',
        'rules' => 'trim|required|max_length[32]'
    ),
);


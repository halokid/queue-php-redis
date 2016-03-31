<?php

// require_once('redis_config.php');
/**
function add_queue($que_name, $i, $str) {
    require_once('redis_config.php');
    // print_r($rds_cfg);
    // exit();
    $rds = new Redis();
    $rds->connect($rds_cfg['host'], $rds_cfg['port']);
    $rds->select(9);
    // $res = $rds->zAdd('aaaa', 'ppopo');
    $res = $rds->zAdd($que_name, $i, $str);
    return $res;
}
**/


class Que{
    function __construct(){}
    
    
    public $_rds;
    
    public function init(){
        include(APPPATH.'config/redis.php');
        // echo APPPATH.'config/redis.php';
        print_r($config);
        // exit();
        $rds = new Redis();
        print_r($rds);
        $rds->connect($config['host'], $config['port']);
        $rds->select(9);
        $this->_rds = $rds;
    }
    
    public function add_queue($que_name, $i, $str) {
        $this->_rds->zAdd($que_name, $i, $str);
    }
    
    public function add_act($que_name, $act) {
        /** act 的值
        test/send_mail##0##1000  
        0 是表示队列处理开始的位置  
        100 是表示每次队列处理的队列数  
        **/
        
        $this->_rds->select(10);
        $keys = $this->_rds->keys('*');
        
        if( !in_array($que_name.'_act', $keys) ) {
            $this->_rds->set($que_name.'_act', $act );
        }
    }
}

?>
<?php



class Que{
    function __construct(){}
    
    
    public $_rds;
    
    public function init(){
        require_once('redis_config.php');
        $rds = new Redis();
        $rds->connect($rds_cfg['host'], $rds_cfg['port']);
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
        $this->_rds->set($que_name.'_act', $act );
    }
}

?>
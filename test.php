<?php
class Test extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    
    //添加进队列
    public function set_queue(){
        $this->add_queue('send_mail', array('82049406@qq.com'), 2);
    }
    
    
    public function send_mail($av, $offset){
            set_time_limit(0);
            
            $row = $this->rev_queue('send_mail', $av, $offset);
            
            if( count($row) != 0   ){
            
            //取出队列执行动作
            //your code here
            
        }
        else {
            echo "没有队列信息了\n";
        }
        
    }
    
    
    /**
     * @author lix
     * @todo 接收队列处理
     * @param 
     * @return 
    */
    public function rev_queue($queue_name='test_queue', $av, $offset) {
        $this->config->load('redis', TRUE);
        // $this->debug_dump($this->config);
        // $this->debug_dump($this->config->item('redis'));
        $cfg = $this->config->item('redis');
        
        $rds = new Redis();
        $rds->connect($cfg['host'], $cfg['port']);
        $rds->select(9);
        $row = $rds->zRange('send_mail', $av, $offset, false);
        // $this->debug_dump($row);
        return $row;
    }   
    
    
    /**
     * @author  lix
     * @todo  加入队列处理
     * @param 
     * @return 
    */
    public function add_queue($queue_name='test_queue', $val_arr=array(), $max_num = 0) {
        $this->config->load('redis', TRUE);
        // $this->debug_dump($this->config);
        $this->debug_dump($this->config->item('redis'));
        $cfg = $this->config->item('redis');
        
        $rds = new Redis();
        $rds->connect($cfg['host'], $cfg['port']);
        $rds->select(9);
        
        for($i=0; $i<$max_num; $i++){
            $rds->zAdd($queue_name, $i, $val_arr[$i] );
        }
        
        return ; 
    }
    
}
























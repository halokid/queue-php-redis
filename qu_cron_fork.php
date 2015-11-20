#!/usr/bin/env php
<?php

if( isset($_SERVER['REMOTE_ADDR'] )) {
	die('NOT REMOTE ADDR');
}

include('Signfork.class.php');
set_time_limit(0);





class test
{
    function __fork($arg)
    {
        // echo "-------------------- 抓取开始 ---------------------";
        
        $t = microtime(true);
        echo $t.":  ".$arg."\n";
        $last_line = system($arg, $res);
        
        
        return( $t.":  ".$arg."\n");
        sleep(3);
    }
}


$test       =new test();
$Signfork   =new Signfork();



//查询队列名称和av
$rds = new Redis();
$rds->connect('172.16.2.3', 6379);


//fork------------

while(true) {
    
    // $keys = array('test/send_mail##0##49');
    // $keys = array('send_mail_act');
    
    echo ".........................waitting for queue........................\n";

    //查询队列名称和av
    $rds->select(10);


    //查询 10 队列里面的动作
    $keys = $rds->keys('*');
    print_r($keys);
    // exit();
    
    foreach( $keys as $k) {

        // $str = $rds->get('send_mail_act');
        $rds->select(10);
        $str = $rds->get($k);
        echo $str."\n";

        $arr = explode('##', $str);
        $av = $arr[1];
        $offset = $arr[2]-1;
        
        $fir_av = $av;  //记录一开始要删除的av， 因为是多进程，不能一段一段的删除，必须全部处理之后，一次过删除所有处理过的key，所以要记录初始av
        $fir_offset = $offset;

        //获取要处理的队列名字
        $q_name_arr = explode('_', $k);
        $q_name = $q_name_arr[0].'_'.$q_name_arr[1];
        echo "the queue name is:    ".$q_name."\n";
        
        
        //初始化每一次执行多进程的的执行数组
        $fork_arr = array();
        
        
        //获取队列的长度
        $rds->select(9);
        $queue_size = $rds->zCard($q_name);
        echo "queue_size:   ".$queue_size."\n";

        while( true ) {
        
            $rds->select(9);
            

            
            //赋值到多进程执行数组
            
            echo "av: ".$av."\n";
            echo "offset: ".$offset."\n";
            echo "\n";
            
            
            
            //组合子进程执行数组, 每次创建3个子进程
            $fork_num = 3;
            
            //其实没必要用这么多临时变量，只是为了好理解
            echo 'av_b:   '.$av."------- offset_b:  ".$offset."\n";
            
            $start = $av;
            $end = $offset;
            
            echo 'start_c:   '.$start."------- end_c:  ".$end."\n";
            
            // $i=0;
            // for($i=$av; $i<$fork_num; $i++) {
            $fork_arr = array();    //重新定义多进程数组，以免数组重复设置
            for($i=0; $i<$fork_num; ++$i) {
                
                // $fork_arr[] = "/usr/bin/php cli.php ".$arr[0].'/'.$av."/".$offset;
                $fork_arr[] = "/usr/bin/php cli.php ".$arr[0].'/'.$start."/".$end;
                // $fork_arr[] = array( "/usr/bin/php cli.php ".$arr[0].'/'.$start."/".$end, $q_name, $start, $end);
                
                echo 'av_xx:   '.$av."------- offset_xx:  ".$offset."\n";
                // if( $i>0){
                $start = $end+1;
                // $end = $start+4;
                $end = $start + $fir_offset;
                // }
                
            }
            
            echo 'start_a:   '.$av."------- end_a:  ".$offset."\n";
            
            //执行组合完成的子进程数组
            $Signfork->run($test,$fork_arr);
            
            
            echo 'start_b:   '.$av."------- end_b:  ".$offset."\n";
            
            $del_start = $av;   //开始删除的位置
            
            //重新设置av， offset
            // $av = $end+1;
            $av = $start;
            // $offset = $av+4;
            $offset = $end;
            
            $del_end = $start-1; //结束删除的位置
            
            echo 'start_a:   '.$start."------- end_a:  ".$end."\n";
            echo 'av_a:   '.$av."------- offset_a:  ".$offset."\n";
            echo 'del_start:   '.$del_start."------- del_end:  ".$del_end."\n";
            echo "----------------------------------------------------------------------------\n";
            echo "----------------------------------------------------------------------------\n";
            echo "----------------------------------------------------------------------------\n";
            
            
            
            
            if( $del_end >= $queue_size ) {
                $rds->del($q_name);
                echo "达到队列最大长度，可以删除队列";
                break;
            }
            
            sleep(2);


        }
        
        //删除队列动作的key
        $rds->select(10);
        $rds->del($k);
        echo 'del queue act:    '.$k."\n";
    }
    
    sleep(3);
    
}


//fork------------





?>

















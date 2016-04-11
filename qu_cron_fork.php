#!/usr/bin/env php
<?php

if( isset($_SERVER['REMOTE_ADDR'] )) {
	die('NOT REMOTE ADDR');
}

// include('Signfork.class.php');
include('./application/libraries/Signfork.class.php');
set_time_limit(0);


class test
{
    function __fork($arg)
    {
        // echo "-------------------- 抓取开始 ---------------------";
        // return file_get_contents($arg);
        
        // $rds = new Redis();
        // $rds->connect('172.16.2.129', 6379);
        // $rds->select(9);
        
        
        // $t = time();
        $t = microtime(true);
        echo $t.":  ".$arg."\n";
        // echo "*********************************\n";
        // echo $t.":  ".$arg[0]."\n";
        // echo $t.":  ".$arg[1]."\n";
        // echo $t.":  ".$arg[2]."\n";
        // echo $t.":  ".$arg[3]."\n";
        // echo "*********************************\n";
        // $last_line = system("/usr/bin/php cli.php ".$arr[0].'/'.$av."/".$offset, $res);
        $last_line = system($arg, $res);
        // $last_line = system($arg[0], $res);
        
        // $del_num = $rds->zRemRangeByRank($q_name, $del_start, $del_end);
        // $del_num = $rds->zRemRangeByRank($arg[1], $arg[2], $arg[3]);
        // $del_num = $rds->zRemRangeByScore($arg[1], $arg[2], $arg[3]);
        
        
        return( $t.":  ".$arg."\n");
        // return( $t.":  ".$arg[0]."\n");
        sleep(3);
    }
}


$test       =new test();
$Signfork   =new Signfork();



//查询队列名称和av
$rds = new Redis();
$rds->connect('172.16.2.129', 6379);


// $arg = array();
// $arg[] = "test/send_mail_act##0##49";
// $arg[] = "test/send_mail_act##50##99";
// $arg[] = "test/send_mail_act##100##149";
// $arg[] = "test/send_mail_act##150##199";
// $arg[] = "test/send_mail_act##200##240";

// $Signfork->run($test, $arg);
// exit();


//fork------------

while(true) {	//for while check the act_queue
    
    // $keys = array('test/send_mail##0##49');
    // $keys = array('send_mail_act');
    
    echo ".........................waitting for queue........................\n";

    //查询队列名称和av
    // $rds = new Redis();
    // $rds->connect('172.16.2.129', 6379);
    $rds->select(10);


    //查询 10 队列里面的动作
    $keys = $rds->keys('*');
    print_r($keys);
    // exit();
    
		foreach( $keys as $k) {		//foreach the act queue
				echo "------------------------------------- 开始循环动作队列 ---------------------------------\n";

        // $str = $rds->get('send_mail_act');
        $rds->select(10);
        $str = $rds->get($k);
        echo $str."\n";

        $arr = explode('##', $str);
        //$av = $arr[1];	//change to  like: xxx/yyy##310##0##100
        $av = $arr[2];
        //$offset = $arr[2]-1;
        $offset = $arr[3]-1;
        
        $fir_av = $av;  //记录一开始要删除的av， 因为是多进程，不能一段一段的删除，必须全部处理之后，一次过删除所有处理过的key，所以要记录初始av
        $fir_offset = $offset;

        //获取要处理的队列名字
        $q_name_arr = explode('_', $k);
				//ex: 从 send_mail_act 获取 send_mail 这个key的名字
        $q_name = $q_name_arr[0].'_'.$q_name_arr[1];
        echo "the queue name is:    ".$q_name."\n";
        
        
        //初始化每一次执行多进程的的执行数组
        $fork_arr = array();
        
        //测试设置, 每次5条队列
        // $av = 0;
        // $offset = 4;
        
        //获取队列的长度
        // $rds->select(9);
        // $queue_size = $rds->zCard($q_name);
        // echo "queue_size:   ".$queue_size."\n";
				

        while( true ) {		//second while, check the queue info list
        
            $rds->select(9);
            
            //查询存在的队列
            // $rds->select(9);
            // $row = $rds->zRange('send_mail', $av, $offset, false);
            // $row = $rds->zRange($q_name, $av, $offset, false);

            
            //赋值到多进程执行数组
            
            echo "av: ".$av."\n";
            echo "offset: ".$offset."\n";
            echo "\n";
            
            // $last_line = system("/usr/bin/php cli.php ".$arr[0].'/'.$av."/".$offset, $res);
            
            
            //组合子进程执行数组, 每次创建3个子进程
            $fork_num = 3;
            
            //其实没必要用这么多临时变量，只是为了好理解
            echo 'av_b:   '.$av."------- offset_b:  ".$offset."\n";
            
            $start = $av;
            $end = $offset;
            
            echo 'start_c:   '.$start."------- end_c:  ".$end."\n";
            
            // $i=0;
            $fork_arr = array();    //重新定义多进程数组，以免数组重复设置
						
						//获取队列的长度
        		$rds->select(9);
        		$queue_size = $rds->zCard($q_name);
        		echo "queue_size:   ".$queue_size."\n";
						if ($queue_size > 0 ) {
						
						
						act_queue_do:
						//when this program finish all queue info, will delete the queue info
            for($i=0; $i<$fork_num; ++$i) {
                
                // $fork_arr[] = "/usr/bin/php cli.php ".$arr[0].'/'.$av."/".$offset;
                // $fork_arr[] = "/usr/bin/php cli.php ".$arr[0].'/'.$start."/".$end;	//单队列正确的执行
								
								$tmp_str = explode('/', $arr[0]);	//ex: backend/bonus_offline
								$que_str = $tmp_str[1].$arr[1];
                $fork_arr[] = "/usr/bin/php cli.php ".$arr[0].'/'.$que_str.'/'.$start."/".$end;	//多队列正确的执行
								
                // $fork_arr[] = array( "/usr/bin/php cli.php ".$arr[0].'/'.$start."/".$end, $q_name, $start, $end);
                
                echo 'av_xx:   '.$av."------- offset_xx:  ".$offset."\n";
                // if( $i>0){
                $start = $end+1;
                // $end = $start+4;
                $end = $start + $fir_offset;
                // }
                
            }
					} else {
						echo "信息队列为空了\n\n";
						break;
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
            
            
            /**
            *删除已经处理的队列
            * $del_num = $rds->zRemRangeByRank('send_mail', $av, $offset);
            * $del_num = $rds->zRemRangeByRank($q_name, $av, $offset);
            * $del_num = $rds->zRemRangeByRank($q_name, $del_start, $del_end);
            * if( $del_num == 0 ){
            *  echo "********************* FINISHED ******************\n";
            * $del_num = $rds->zRemRangeByRank($q_name, $fir_av, $del_end);
            *    $rds->del($q_name);
            *    break;
            *} else {
            *    echo "--------del number is:  ".$del_num."----------\n";
            *}
            **/
				
					  
            // dont del the act queue, maybe it will add queue info when you run
						// this program, so we check again the act queue size
						// /**
            if( $del_end >= $queue_size ) {
                $rds->del($q_name);
                echo "达到队列最大长度，可以删除队列";
                break;
            }
						// **/
						/**
        		$queue_size_check = $rds->zCard($q_name);
						if ($del_end >= $queue_size_check) {
							//if has deal queue info number is m,ore than new queue size
							$rds->del($q_name);
              echo "重新检查了队列长度，达到队列最大长度，可以删除队列";
							break;
						} else {
							goto act_queue_do;
						}
						**/
						
            
            sleep(2);

            // $av = $offset+1;
            // $offset = $av + $fir_offset;

        }		// END second while
        
        //删除队列动作的key
		//TODO:	不要删除队列，删除会有并发操作，操作队列不完全的问题
        // $rds->select(10);
        // $rds->del($k);
        // echo 'del queue act:    '.$k."\n";
        echo 'DONOT DELETE act queue:    '.$k."\n";
    }	//END foreach act queue
    
    sleep(3);
    
}	// END first while


//fork------------




/**

//查询队列名称和av
$rds = new Redis();
$rds->connect('172.16.2.129', 6379);

while(true){
    echo ".........................waitting for queue........................\n";

    //查询队列名称和av
    // $rds = new Redis();
    // $rds->connect('172.16.2.129', 6379);
    $rds->select(10);


    //查询 10 队列里面的动作
    $keys = $rds->keys('*');
    print_r($keys);
    // exit();


    //开始多进程处理队列


    foreach( $keys as $k) {

        // $str = $rds->get('send_mail_act');
        $str = $rds->get($k);
        echo $str."\n";

        $arr = explode('##', $str);
        $av = $arr[1];
        $offset = $arr[2]-1;

        $fir_offset = $offset;

        //获取要处理的队列名字
        $q_name_arr = explode('_', $k);
        $q_name = $q_name_arr[0].'_'.$q_name_arr[1];
        echo "the queue name is:    ".$q_name."\n";

        while( true ) {


            //查询存在的队列
            $rds->select(9);
            // $row = $rds->zRange('send_mail', $av, $offset, false);
            $row = $rds->zRange($q_name, $av, $offset, false);

            echo "av: ".$av."\n";
            echo "offset: ".$offset."\n";

            // print_r($row);
            echo "\n";


            
            //define('STDIN', TRUE);
            // $_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'] = $argv[1];
            // include dirname(__FILE__).'/index.php';

            // popen("/usr/bin/php cli.php test/test_cli/".$av."/".$offset, "r");
            // popen("/usr/bin/php cli.php test/send_mail/".$av."/".$offset, "r");
            // $last_line = system("/usr/bin/php cli.php test/send_mail/".$av."/".$offset, $res);
            $last_line = system("/usr/bin/php cli.php ".$arr[0].'/'.$av."/".$offset, $res);
            // print_r($res);
            // exec("/usr/bin/php cli.php test/test_cli/");

            
            //删除已经处理的队列
            // $del_num = $rds->zRemRangeByRank('send_mail', $av, $offset);
            $del_num = $rds->zRemRangeByRank($q_name, $av, $offset);
            if( $del_num == 0 ){
                echo "********************* FINISHED ******************\n";
                $rds->del($q_name);
                break;
            } else {
                echo "--------del number is:  ".$del_num."----------\n";
            }
            
            sleep(5);
            
            

            $av = $offset+1;
            $offset = $av + $fir_offset;

            

        }
        
        //删除队列动作的key
        $rds->select(10);
        $rds->del($k);
        echo 'del queue act:    '.$k."\n";
    }
    
    sleep(3);

}


**/


?>


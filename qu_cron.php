#!/usr/bin/env php
<?php

if( isset($_SERVER['REMOTE_ADDR'] )) {
	die('NOT REMOTE ADDR');
}

set_time_limit(0);


 //查询队列名称和av
$rds = new Redis();
$rds->connect('172.16.2.129', 6379);

while(true){
    echo ".........................waitting for queue........................\n";

    $rds->select(10);


    //查询 10 队列里面的动作
    $keys = $rds->keys('*');
    print_r($keys);
    // exit();




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
        // print_r($q_name_arr);
        // exit();
        $q_name = $q_name_arr[0].'_'.$q_name_arr[1];
        echo "the queue name is:    ".$q_name."\n";

        while( true ) {


            //查询存在的队列
            $rds->select(9);

            echo "av: ".$av."\n";
            echo "offset: ".$offset."\n";

            // print_r($row);
            echo "\n";


            
            $last_line = system("/usr/bin/php cli.php ".$arr[0].'/'.$av."/".$offset, $res);

            
            //删除已经处理的队列
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
?>

















<?php
/**
 * Project: Signfork: php多线程库
 * File:    Signfork.class.php
 *
 * <a href="http://my.oschina.net/link1212" target="_blank" rel="nofollow">@link</a>    http://code.google.com/p/signfork/
 * <a href="http://my.oschina.net/arthor" target="_blank" rel="nofollow">@author</a>    lajabs <hittyo at gmail dot com> QQ:124321697
 * @version 1.0.0 2009/8/4
 */
 
 
 
class Signfork
{
    /**
     * 设置子进程通信文件所在目录
     * <a href="http://my.oschina.net/var" target="_blank" rel="nofollow">@var</a>  string
     */
    private $tmp_path='/tmp/';
 
    /**
     * Signfork引擎主启动方法
     * 1、判断$arg类型,类型为数组时将值传递给每个子进程;类型为数值型时,代表要创建的进程数.
     * @param object $obj 执行对象
     * @param string|array $arg 用于对象中的__fork方法所执行的参数
     * 如:$arg,自动分解为:$obj->__fork($arg[0])、$obj->__fork($arg[1])...
     * <a href="http://my.oschina.net/u/556800" target="_blank" rel="nofollow">@return</a>  array  返回   array(子进程序列=>子进程执行结果);
     */
    public function run($obj,$arg=1)
    {
        if(!method_exists($obj,'__fork'))
        {
            exit("Method '__fork' not found!");
        }
 
        if(is_array($arg))  //如果为数组类型，则根据数组来创建进程，进程是同时执行的
        {
            // print_r($arg);
            // exit();
            $i=0;
            foreach($arg as $key=>$val)
            {
                $spawns[$i]=$key;
                $i++;
                $this->spawn($obj,$key,$val);   //在一步进程已经开始执行了，是同时执行的
            }
            $spawns['total']=$i;    //这个键是表示一共开了多少个进程
            // print_r($spawns);
            // exit();
        }
        elseif($spawns=intval($arg))    //如果是整型，则代表要创建的进程数
        {
            for($i = 0; $i < $spawns; $i++) 
            {
                $this->spawn($obj,$i);
            }
        }
        else    //非法的参数
        {
            exit('Bad argument!');
        }
        if($i>1000) exit('Too many spawns!');
 
        return $this->request($spawns); //等待各个子进程的结果，假如需要获取每个子进程的结果，才用到这个，可以在函数里面控制子进程返回的结果逻辑
    }
 
 
    /**
     * Signfork主进程控制方法
     * 1、$tmpfile 判断子进程文件是否存在，存在则子进程执行完毕，并读取内容
     * 2、$data收集子进程运行结果及数据，并用于最终返回
     * 3、删除子进程文件
     * 4、轮询一次0.03秒，直到所有子进程执行完毕，清理子进程资源
     * @param  string|array $arg 用于对应每个子进程的ID
     * <a href="http://my.oschina.net/u/556800" target="_blank" rel="nofollow">@return</a>  array  返回   array([子进程序列]=>[子进程执行结果]);
     */
    private function request($spawns)
    {
        $data=array(); //子进程结果初始化
        $i=is_array($spawns) ? $spawns['total'] : $spawns;  //获得创建的子进程的数量
        for($ids = 0; $ids<$i; $ids++)
        {
            while(!($cid=pcntl_waitpid(-1, $status, WNOHANG)))usleep(30000);    //如果子进程不返回状态，则等待0.03秒再轮询获取返回
            $tmpfile=$this->tmp_path.'sfpid_'.$cid;
            $data[$spawns['total'] ? $spawns[$ids] : $ids] = file_get_contents($tmpfile); //从子进程结果文件中获取子进程执行逻辑返回的内容
            // unlink($tmpfile);
        }
        print_r($data);
        return $data;
    }
 
    /**
     * Signfork子进程执行方法
     * 1、pcntl_fork 生成子进程
     * 2、file_put_contents 将'$obj->__fork($val)'的执行结果存入特定序列命名的文本
     * 3、posix_kill杀死当前进程
     * @param object $obj   待执行的对象
     * @param object $i     子进程的序列ID，以便于返回对应每个子进程数据
     * @param object $param 用于输入对象$obj方法'__fork'执行参数
     */
    private function spawn($obj,$i,$param=null)
    {
        if(pcntl_fork()===0)
        {
            $cid=getmypid();    //获取本身的ID（子进程的ID）
            file_put_contents($this->tmp_path.'sfpid_'.$cid,$obj->__fork($param)); //把执行的逻辑的结果写入到执行的子进程结果文件
            posix_kill($cid, SIGTERM);  //杀死子进程
            exit;   //退出加快回收内存
        }
    }
}
 
?>









 1, add queue information


2, queue information stored in redis, I set is 10 libraries for the queue motion information, 9 repository queue for detailed information


3, the server daemon constantly receive queue, processing queue again, the current code of logic is that cycle receive 10 libraries queue information first, then segment multi-process processing queue details of library






Sample code calls:


1, add the queue (such as adding email) :


$q - > add_queue (' send_mail '$I, "hello world");






2, server-side processing queue logic, first read queue motion information, process processing, the current version is not handle multiple queue motion information, at the same time can only process more processed a queue motion information inside of each queue, after processing another queue motion information again.(server version has a single process, multiple processes two versions)


Server, running on the server cli environment


PHP qu_cron. PHP;


Or multi-process qu_cron_fork PHP. PHP


Now call the cli. PHP because processing queue logic is written in codeigniter, for reference only ﻿ 1, add queue information


2, queue information stored in redis, I set is 10 libraries for the queue motion information, 9 repository queue for detailed information


3, the server daemon constantly receive queue, processing queue again, the current code of logic is that cycle receive 10 libraries queue information first, then segment multi-process processing queue details of library






Sample code calls:


1, add the queue (such as adding email) :


$q - > add_queue (' send_mail '$I, "hello world");






2, server-side processing queue logic, first read queue motion information, process processing, the current version is not handle multiple queue motion information, at the same time can only process more processed a queue motion information inside of each queue, after processing another queue motion information again.(server version has a single process, multiple processes two versions)


Server, running on the server cli environment


PHP qu_cron. PHP;


Or multi-process qu_cron_fork PHP. PHP


Now call the cli. PHP because processing queue logic is written in codeigniter, for reference only

/** ======================================================================================================= **/

﻿1， 添加队列信息

2， 队列信息储存在redis里面，我设置的是 10库为队列动作信息，9库为队列详细的信息

3， 服务端守护进程不断接收队列，再处理队列，目前代码的逻辑是，先循环接收10库的队列动作信息，再分段多进程处理9库的队列详细信息



代码调用范例：

1, 添加队列(比如添加发送邮件)：

$q->add_queue('send_mail', $i, ‘hello world');



2, 服务端处理队列逻辑，先读取队列动作信息，再多进程处理，目前的版本是不能同时处理多个队列动作信息，只能多进程处理完一条队列动作信息里面的每条队列信息之后，再处理另外一条队列动作信息。（服务端的版本有单进程，多进程两个版本）

服务端使用,在服务端cli环境下运行

php qu_cron.php;

或者多进程 php qu_cron_fork.php

目前调用的cli.php 是因为处理队列的逻辑是用codeigniter写的，仅作参考









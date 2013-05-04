<?php

router('task.constant',function(){

	$constant = model('constant');
	$time = time();
	$list = $constant->listGet("status = 'start' AND last_watch_time + period < {$time}");
	foreach ($list as $key => $value) {
		$constant->check($value['constant_id']);
	}

	//print_r($list);

});

router('task.server',function(){

	$server = model('server');
	$time = time();
	$list = $server->listGet("last_watch_time + period < {$time}");
	foreach ($list as $key => $value) {
		$server->check($value['server_id']);
	}

});


?>
<?php

router('server.add',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$ip =  filter('ip', '/^([0-9]{1,3}.){3}[0-9]{1,3}$/', 'IP格式错误');

	$server = model('server');
	$info = $server->get($ip, 'ip');

	if(!empty($info)) json(false, '该服务器IP已经被添加');

	$result = $server->add($ip, $user_id);
	if($result === false) json(false, '添加失败');
	json(true, '添加成功');

});

router('server.info',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$update =  filter('update', '/^true|false$/', '是否进行更新');
	$server_id =  filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');

	$server = model('server');
	$serverInfo = $server->get($server_id);
	if($serverInfo['user_id'] != $user_id) json(false, '不能访问他人server');

	if($update == 'true'){
		$new = $server->updateInfo($server_id);
		$serverInfo = array_merge($serverInfo, $new);
	}

	$serverInfo['last_netstat'] = jdecode($serverInfo['last_netstat']);
	$serverInfo['last_run'] = jdecode($serverInfo['last_run']);
	$serverInfo['last_device'] = jdecode($serverInfo['last_device']);
	$serverInfo['last_cpu'] = jdecode($serverInfo['last_cpu']);
	$serverInfo['last_memory'] = jdecode($serverInfo['last_memory']);
	$serverInfo['last_disk'] = jdecode($serverInfo['last_disk']);
	$serverInfo['last_network'] = jdecode($serverInfo['last_network']);
	json(true, $serverInfo);

});

router('server.disk',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$server_id =  filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');
	$name =  filter('name', '/^.{0,255}$/', '磁盘名字格式错误');
	$time_unit = filter('time_unit', '/^day|month|year$/', '时间单位错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	$name = base64_encode($name);

	$server = model('server');
	$result = $server->diskGet($server_id, $name, $time_unit, $start_time, $stop_time);
	json(true, $result);

});

router('server.network',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$server_id =  filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');
	$descr =  filter('descr', '/^.{0,255}$/', '网卡描述名称格式错误');
	$time_unit = filter('time_unit', '/^day|month|year$/', '时间单位错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	$descr = base64_encode($descr);

	$server = model('server');
	$result['data'] = $server->networkGet($server_id, $descr, $time_unit, $start_time, $stop_time);
	$result['summary'] = $server->networkSummary($server_id, $descr, $start_time, $stop_time);
	json(true, $result);

});

router('server.cpu',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$server_id =  filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');
	$time_unit = filter('time_unit', '/^day|month|year$/', '时间单位错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	$server = model('server');
	$result = $server->cpuGet($server_id, $time_unit, $start_time, $stop_time);
	json(true, $result);

});

router('server.memory',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$server_id =  filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');
	$time_unit = filter('time_unit', '/^day|month|year$/', '时间单位错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	$server = model('server');
	$result = $server->memoryGet($server_id, $time_unit, $start_time, $stop_time);
	json(true, $result);

});

router('server.run',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$server_id =  filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');
	$time_unit = filter('time_unit', '/^day|month|year$/', '时间单位错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	$server = model('server');
	$result = $server->runGet($server_id, $time_unit, $start_time, $stop_time);
	json(true, $result);

});

router('server.netstat',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$server_id =  filter('server_id', '/^[0-9]{1,9}$/', 'server_id格式错误');
	$time_unit = filter('time_unit', '/^day|month|year$/', '时间单位错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	$server = model('server');
	$result = $server->netstatGet($server_id, $time_unit, $start_time, $stop_time);
	json(true, $result);

});


?>
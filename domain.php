<?php

router('domain.add',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$domain = filter('domain', '/^[a-zA-z0-9\-\.]+$/', '域名格式错误');

	$domainModel = model('domain');
	$constantModel = model('constant');
	$info = $domainModel->get($domain, 'domain');

	if(!empty($info)) json(false, '该域名已经被添加');

	$result = $domainModel->add($domain, $user_id);
	if($result == false) json(false, '添加失败');
	$constantModel->add($result);
	json(true, '添加成功');

});

router('domain.info',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$domain_id = filter('domain_id', '/^[0-9]{1,9}$/', '域名ID错误');

	$domainModel = model('domain');
	$result = $domainModel->get($domain_id);
	if(empty($result)) json(false, '域名ID对应域名为空');
	if($result['server_id'] != 0){
		$serverModel = model('server');
		$server = $serverModel->get($result['server_id']);
		if(!empty($server)) $result['server'] = $server;
	}
	$constantModel = model('constant');
	$constant = $constantModel->get($domain_id, 'domain_id');
	$result['constant'] = array();
	if(!empty($constant)){
		$result['constant'] = $constant;
	}

	json(true, $result);

});

router('domain.setServer',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$domain_id = filter('domain_id', '/^[0-9]{1,9}$/', '域名ID错误');	
	$server_id = filter('server_id', '/^[0-9]{1,9}$/', '服务器ID错误');

	$domainModel = model('domain');
	$find = $domainModel->get($domain_id);
	if($find['user_id'] != $user_id) json(false, '无权操作该域名');

	$serverModel = model('server');
	$find = $serverModel->get($server_id);
	if($find['user_id'] != $user_id) json(false, '无权操作该服务器');

	$updateArray = array('server_id' => $server_id);
	$result = $domainModel->update($domain_id, $updateArray);

	if($result == 0) json(false, '未进行更新');
	json(true, '更新成功');
});

router('domain.setName',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$domain_id = filter('domain_id', '/^[0-9]{1,9}$/', '域名ID错误');
	$name = filter('name', '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\s\']{0,255}$/u', '别名格式错误(a-zA-Z0-9汉字)');


	$domainModel = model('domain');
	$find = $domainModel->get($domain_id);
	if($find['user_id'] != $user_id) json(false, '无权操作');

	$updateArray = array('custom_name' => $name);
	$result = $domainModel->update($domain_id, $updateArray);

	if($result == 0) json(false, '未进行更新');
	json(true, '更新成功');


});

router('domain.setPort',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$domain_id = filter('domain_id', '/^[0-9]{1,9}$/', '域名ID错误');	
	$port = filter('port', '/^[0-9]{1,6}$/', '端口格式错误');


	$domainModel = model('domain');
	$find = $domainModel->get($domain_id);
	if($find['user_id'] != $user_id) json(false, '无权操作');

	$updateArray = array('port' => (int)$port);
	$result = $domainModel->update($domain_id, $updateArray);

	if($result == 0) json(false, '未进行更新');
	json(true, '更新成功');

});

?>
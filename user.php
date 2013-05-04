<?php

router('user.login',function(){
	$mail = filter('mail', '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', '邮箱格式不符');
	$pass = filter('pass', '/^.{6,30}$/', '密码需要为6-30位字符');

	$user = model('user');
	$info = $user->get($mail, 'mail');

	if(empty($info)) json(false, '邮箱不存在，登录失败');

	$enPass = $user->passEncode($pass, $info['usalt']);
	if(strcasecmp($enPass, $info['passwd']) !== 0) json(false, '邮箱或密码错误，登录失败');

	$user->login($info['user_id']);
	json(true, '登录成功');
});

router('user.reg',function(){
	$mail = filter('mail', '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', '邮箱格式不符');
	$pass = filter('pass', '/^.{6,30}$/', '密码需要为6-30位字符');

	$user = model('user');
	$info = $user->get($mail, 'mail');

	if(!empty($info)) json(false, '该邮箱已注册');

	$user_id = $user->creat($mail, $pass);
	if($user_id === false) json(false, '创建失败');

	$user->login($user_id);
	json(true, '创建成功');
});

?>
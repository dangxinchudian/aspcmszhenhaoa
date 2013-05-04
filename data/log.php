<?php

router('log.daily',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$domain_id = filter('domain_id', '/^[0-9]{1,9}$/', '域名ID格式错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}+$/', '起始日期格式错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}+$/', '结束日期格式错误');

	$domain = 'www.firefoxbug.net';
	/*$start_time = time() - 60*60*24*5;
	$stop_time = time();*/

	$aws = model('aws');
	$result = $aws->daily($domain, $start_time, $stop_time);
	if($result === false) json(false, '该域名不存在');

	/*$return = array();
	$summary = array(
		'visits' => 0,
		'pages' => 0,
		'hits' => 0,
		'bandwidth' => 0
	);
	foreach ($result as $key => $value) {
		$return[$value['day']] = $value;
		$summary['visits'] = $summary['visits'] + $value['visits'];
		$summary['pages'] = $summary['pages'] + $value['pages'];
		$summary['hits'] = $summary['hits'] + $value['hits'];
		$summary['bandwidth'] = $summary['bandwidth'] + $value['bandwidth'];
	}
	$summary['avg_visits'] = $summary['visits'] / count($result);
	$summary['avg_pages'] = $summary['pages'] / count($result);
	$summary['avg_hits'] = $summary['hits'] / count($result);
	$summary['avg_bandwidth'] = $summary['bandwidth'] / count($result);*/
	$return['data'] = $result;
	$return['summary'] = $aws->summary($result);
	json(true, $return);

});

router('log.browser',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$domain_id = filter('domain_id', '/^[0-9]{1,9}$/', '域名ID格式错误');
	$time = filter('time', '/^[0-9]{1,10}+$/', '日期格式错误');

	$domain = 'www.firefoxbug.net';
	/*$time = time() - 60*60*24*10;*/

	$aws = model('aws');
	$result = $aws->browser($domain, $time);
	if($result === false) json(false, '该域名不存在');

	$return = array();
	foreach ($result as $key => $value) {
		$name = preg_replace('/[0-9\.]/', '', $value['name']);
		//$result[$key]['a'] = preg_replace('/[0-9\.]/', '', $value['name']);
		if(isset($return[$name])){
			$return[$name] = $return[$name] + $value['hits'];
		}else{
			$return[$name] = $value['hits'];
			//echo $name;
		}
	}

	json(true, $return);

});

router('log.os',function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$domain_id = filter('domain_id', '/^[0-9]{1,9}$/', '域名ID格式错误');
	$time = filter('time', '/^[0-9]{1,10}+$/', '日期格式错误');

	$domain = 'www.firefoxbug.net';
	//$time = time() - 60*60*24*10;

	$aws = model('aws');
	$result = $aws->os($domain, $time);
	if($result === false) json(false, '该域名不存在');

	$return = array();
	foreach ($result as $key => $value) {
		$return[$value['name']] = $value['hits'];
	}

	json(true, $return);

});


router('log.general', function(){

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$domain_id = filter('domain_id', '/^[0-9]{1,9}$/', '域名ID格式错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}+$/', '起始日期格式错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}+$/', '结束日期格式错误');

	$domain = 'www.firefoxbug.net';
	/*$start_time = time() - 60*60*24*5;
	$stop_time = time();*/

	$aws = model('aws');
	$result = $aws->general($domain, $start_time, $stop_time);
	if($result === false) json(false, '该域名不存在');

	$return['data'] = $result;
	$return['summary'] = $aws->summary($result);
	json(true, $return);
});

?>
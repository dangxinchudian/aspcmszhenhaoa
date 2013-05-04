<?php


router('constant.active',function(){		//监控打开关闭

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$constant_id = filter('constant_id', '/^[0-9]{1,9}$/', '监测ID错误');	
	$active = filter('active', '/^start|stop$/', '监测动作格式错误');

	$constant = model('constant');
	$updateArray = array('status' => $active);
	$result = $constant->update($constant_id, $updateArray);
	if($result > 0) json(true, '更改监控状态成功');
	json(false, '未更改');

});

router('constant.list',function(){		//中断监测列表

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$start = 0;
	$limit = 10;

	$start = filter('start', '/^[0-9]{1,9}$/', '起始位置格式错误');
	$limit = filter('limit', '/^[0-9]{1,9}$/', '偏移格式错误');
	if($limit <= 0) $limit = 1;

	$constantModel = model('constant');
	$list = $constantModel->userGet($user_id, $start, $limit);
	foreach ($list as $key => $value) {
		$list[$key]['available'] = $constantModel->available($value['constant_id'], $value['creat_time']);
		$list[$key]['3dayfault'] = $constantModel->faultTime($value['constant_id'], time() - 3600*24*3, time());
	}
	$count = $constantModel->userCount($user_id);
	$array = array(
		'start' => $start,
		'limit' => $limit,
		'list' => $list,
		'total' => $count 
	);
	json(true, $array);

});

router('constant.get',function(){		//中断监测单个获得

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$constant_id = filter('constant_id', '/^[0-9]{1,9}$/', '监测ID错误');	

	$constantModel = model('constant');
	$result = $constantModel->get($constant_id);
	if(empty($result)) json(false, '监测ID对应对象为空');

	$result['work_time'] = time() - $result['creat_time'];
	$result['fault_count'] = $constantModel->faultCount($constant_id, $result['creat_time'], time());
	$result['3dayfault'] = $constantModel->faultTime($constant_id, time() - 3600*24*3, time());
	$result['all_fault_time'] = $constantModel->faultTime($constant_id, $result['creat_time'], time());

	json(true, $result);

});


router('constant.detail',function(){		//中断监测图表绘制

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$constant_id = filter('constant_id', '/^[0-9]{1,9}$/', '监测ID错误');
	$time_unit = filter('time_unit', '/^day|month|year$/', '时间单位错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');

	$constantModel = model('constant');
	$result = $constantModel->dataGet($constant_id, $time_unit, $start_time, $stop_time);	
	json(true, $result);

});

router('constant.fault',function(){		//故障历史

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$constant_id = filter('constant_id', '/^[0-9]{1,9}$/', '监测ID错误');
	$start_time = filter('start_time', '/^[0-9]{1,10}$/', '起始时间单位错误');
	$stop_time = filter('stop_time', '/^[0-9]{1,10}$/', '结束时间单位错误');
	$start = filter('start', '/^[0-9]{1,10}$/', '起始位置格式错误');
	$limit = filter('limit', '/^[0-9]{1,10}$/', '偏移位置格式错误');

	$constantModel = model('constant');
	$result = $constantModel->faultList($constant_id, $start_time, $stop_time, $start, $limit);
	json(true, $result);

});

?>
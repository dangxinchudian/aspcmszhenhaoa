<?php
/*Twwy's art*/

date_default_timezone_set('PRC');

preg_match('/\/data\/(.+)$/', $_SERVER['REQUEST_URI'], $match);
//preg_match('/\/data\/(.+)$/', $_SERVER['REQUEST_URI'], $match);
$uri = (empty($match)) ? 'default' : $match[1];

/*数据库*/
require('./database.php');
$db = new database;

/*路由*/
$router = Array();
function router($path, $func){
	global $router;
	$router[$path] = $func;
}

/*视图*/
/*function view($page, $data = Array(), $onlyBody = false){
	foreach ($data as $key => $value) $$key = $value;
	if($onlyBody) return require("./view/{$page}");
	require("./view/header.html");
	require("./view/{$page}");
	require("./view/footer.html");
}*/

/*会话*/
session_start();

/*JSON格式*/
function json($result, $value){
	if($result) exit(json_encode(array('result' => true, 'data' => $value)));
	exit(json_encode(array('result' => false, 'msg' => $value)));
}

/*POST过滤器*/	//符合rule返回字符串，否则触发callback，optional为真则返回null
function filter($name, $rule, $callback, $optional = false){
	if(isset($_POST[$name]) && preg_match($rule, $post = trim($_POST[$name]))) return $post;
	elseif(!$optional){
		if(is_object($callback)) return $callback();
		else json(false, $callback);
	}
	return null;
}

/*模型*/
class model{
	function db(){
		global $db;
		return $db;
	}
}//model中转db类
function model($value){
	require("./model/{$value}.php");
	return new $value;
}

/*扩展函数*/
require('common.php');
require('curl.php');

/*================路由表<开始>========================*/

require('user.php');
require('domain.php');
require('constant.php');
require('server.php');
require('task.php');
//require('user.php');
//require('domain.php');
//require('task.php');


router('test',function(){
	echo '<form method="POST" action="./user.login"><input name="mail" value="zje2008@qq.com"/><input name="pass" value="b123456"/><input type="submit"/></form>';
});

router('test2',function(){
	echo '<form method="POST" action="./domain.add"><input name="domain" value="test.com"/><input type="submit"/></form>';
});

router('test3', function(){
	print_r(httpHeader('http://facebook.com'));
});

router('test4', function(){
	$constant = model('constant');
	$constant->check(2);
});

router('test5',function(){
	echo '<form method="POST" action="./server.add"><input name="ip" value="127.0.0.1"/><input type="submit"/></form>';
});


router('test6',function(){
	echo '<form method="POST" action="./domain.setPath"><input name="domain_id" value="1"/><input name="path" value="/hah" /><input type="submit"/></form>';
});

router('test7',function(){
	echo '<form method="POST" action="./domain.setServer"><input name="domain_id" value="1"/><input name="server_id" value="1" /><input type="submit"/></form>';
});

router('test8',function(){
	echo '<form method="POST" action="./domain.info"><input name="domain_id" value="1"/><input type="submit"/></form>';
});

router('test9',function(){
	echo '<form method="POST" action="./constant.active"><input name="constant_id" value="1"/><input name="active" value="stop"><input type="submit"/></form>';
});

router('test10',function(){
	echo '<form method="POST" action="./constant.get"><input name="constant_id" value="1"/><input type="submit"/></form>';
});

router('test11',function(){
	$constantModel = model('constant');
	$constantModel->dataGet(2, 'day', 1226506988, time());
});

router('test12',function(){
	$server = model('server');
	$server->updateInfo(2);
});

router('test13',function(){
	echo '<form method="POST" action="./server.info"><input name="server_id" value="2"/><input name="update" value="false"/><input type="submit"/></form>';
});

router('test14',function(){
	$server = model('server');
	print_r($server->check(2));
});

/*router('about',function(){
	$data = array('view' => 'about');
	view('about.html', $data);
});

router('performance',function(){
	$data = array('view' => 'performance');
	view('performance.html', $data);
});

router('scan',function(){
	$data = array('view' => 'scan');
	view('scan.html', $data);
});

router('window.website.add',function(){
	view('/window/website-add.html', array(), true);
});*/

/*router('main',function(){
	view('main.html', array());
});*/


/*================路由表<结束>========================*/


/*路由遍历*/
foreach ($router as $key => $value){
	if(preg_match('/^'.$key.'$/', $uri, $matches)) exit($value($matches));
}

/*not found*/
echo 'Page not fonud';

?>

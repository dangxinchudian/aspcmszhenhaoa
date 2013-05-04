<?php

class Curl{
 
	//CURL句柄
	private $curl = null;
	//CURL SETOPT 信息
	private $setopt = array(
		'port'=>80,
		'timeOut'=>30,
		'userAgent'=>'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5'
	);
	
	/** 构造函数 */
	public function __construct($setopt=array()) {
		//合并用户的设置和系统的默认设置
		$this->setopt = array_merge($this->setopt, $setopt);
		//如果没有安装CURL则终止程序
		function_exists('curl_init') || die('CURL Library Not Loaded');
		//初始化
		$this->curl = curl_init();
		
		//设置CURL连接的端口
		curl_setopt($this->curl, CURLOPT_HEADER, true);
		curl_setopt($this->curl, CURLOPT_NOBODY, true);
		curl_setopt($this->curl, CURLOPT_HTTPGET, true);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($this->curl, CURLOPT_PORT, $this->setopt['port']);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->setopt['timeOut']);
		curl_setopt($this->curl, CURLOPT_USERAGENT, $this->setopt['userAgent']);
	}
	
	public function get($url, $referer='', $params=array()) {
		return $this->_request('GET', $url, $referer, $params);
	}
	
	public function post($url, $referer='', $params=array()) {
		return $this->_request('POST', $url, $referer, $params);
	}
	
	private function _request($method, $url, $referer='', $params=array()) {
		
		//如果是以GET方式请求则要连接到URL后面
		if($method == 'GET'){
			$url = $this->_parseUrl($url,$params);
		}
		//如果是POST
		if($method == 'POST'){
			//发送一个常规的POST请求，类型为：application/x-www-form-urlencoded
			curl_setopt($this->curl, CURLOPT_POST, true) ;
			//设置POST字段值
			$postData = $this->_parsmEncode($params,false);
			//pr($postData); die;
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData);
		}
		//设置了引用页,否则自动设置
		if($referer){
			curl_setopt($this->curl, CURLOPT_REFERER, $referer);
		}else{
			curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
		}
		
		//设置请求的URL
		curl_setopt($this->curl, CURLOPT_URL, $url);
		
		//开始执行请求
		curl_exec($this->curl);
		
		//得到所有设置的信息
		$curlinfo = curl_getinfo($this->curl);

		$info['requtime'] 	= time();
		$info['requdate'] 	= date("Y-m-d H:i:s");
		
		$info['resptime'] 	= $curlinfo['total_time'];
		$info['respstatus']	= $curlinfo['http_code'];
		$info['respresult']	= $this->_httpcode($curlinfo['http_code']);

		$info['hostpoint']	= 'local';
		
		curl_close($this->curl);
	
		return $info;
	}
	
	private function _parseUrl($url, $params) {
		$fieldStr = $this->_parsmEncode($params);
		if($fieldStr){
			$url .= strstr($url,'?')===false ? '?' : '&';
			$url .= $fieldStr;
		}
		return $url;
	}
	
	private function _parsmEncode($params, $isRetStr=true) {
		$fieldStr = '';
		$spr = '';
		$result = array();
		foreach($params as $key=>$value){
			$value = urlencode($value);
			$fieldStr .= $spr.$key .'='. $value;
			$spr = '&';
			$result[$key] = $value;
		}
		return $isRetStr ? $fieldStr : $result;
	}

	private function _httpcode($code=0) {
		# Informational 1xx
		$hc['0'] = 'Unable to access'; 
		$hc['100'] = 'Continue'; 
		$hc['101'] = 'Switching Protocols';
		# Successful 2xx 
		$hc['200'] = 'OK';
		$hc['201'] = 'Created';
		$hc['202'] = 'Accepted';
		$hc['203'] = 'Non-Authoritative Information';
		$hc['204'] = 'No Content';
		$hc['205'] = 'Reset Content';
		$hc['206'] = 'Partial Content';
		# Redirection 3xx 
		$hc['300'] = 'Multiple Choices';
		$hc['301'] = 'Moved Permanently';
		$hc['302'] = 'Found';
		$hc['303'] = 'See Other';
		$hc['304'] = 'Not Modified';
		$hc['305'] = 'Use Proxy';
		$hc['306'] = '(Unused)';
		$hc['307'] = 'Temporary Redirect';
		# Client Error 4xx 
		$hc['400'] = 'Bad Request';
		$hc['401'] = 'Unauthorized';
		$hc['402'] = 'Payment Required';
		$hc['403'] = 'Forbidden';
		$hc['404'] = 'Not Found';
		$hc['405'] = 'Method Not Allowed';
		$hc['406'] = 'Not Acceptable';
		$hc['407'] = 'Proxy Authentication Required';
		$hc['408'] = 'Request Timeout';
		$hc['409'] = 'Conflict';
		$hc['410'] = 'Gone';
		$hc['411'] = 'Length Required';
		$hc['412'] = 'Precondition Failed';
		$hc['413'] = 'Request Entity Too Large';
		$hc['414'] = 'Request-URI Too Long';
		$hc['415'] = 'Unsupported Media Type';
		$hc['416'] = 'Requested Range Not Satisfiable';
		$hc['417'] = 'Expectation Failed';
		# Server Error 5xx
		$hc['500'] = 'Internal Server Error';
		$hc['501'] = 'Not Implemented';
		$hc['502'] = 'Bad Gateway';
		$hc['503'] = 'Service Unavailable';
		$hc['504'] = 'Gateway Timeout';
		$hc['505'] = 'HTTP Version Not Supported';
		
		return $hc[$code];
	}
	
}
?>
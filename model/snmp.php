<?php

class snmp extends model{
	public function check($domain_id){
		$url = $this->path($domain_id);
		if(!$url) return false;
		$result = httpHeader($url);
		$insertArray = array(
			'domain_id' => $domain_id,
			'request_time' => $result['time'],
			'insert_time' => time(),
			'request_status' => $result['code'],
			'request_result' => $result['status']
		);
		$result = $this->db()->insert($this->table(), $insertArray);
		if($result == 0) return false;
	}

	private function table(){		//判断表是否存在,不存在的话,创建。返回表名。
		$year = date('Y');
		$name = "snmp_{$year}";
		/*$sql = "CREATE TABLE IF NOT EXISTS `{$name}` (
				  `constant_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `domain_id` int(10) unsigned NOT NULL,
				  `request_time` decimal(9,2) unsigned NOT NULL,
				  `request_result` varchar(255) NOT NULL,
				  `request_status` int(10) unsigned NOT NULL,
				  `insert_time` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`constant_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$this->db()->query($sql, 'exec');*/
		return $name;
	}

}



?>
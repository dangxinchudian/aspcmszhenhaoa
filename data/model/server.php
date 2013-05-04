<?php
class server extends model{

	public function add($ip, $user_id){
		$insertArray = array(
			'ip' => $ip, 
			'user_id' => $user_id,
			'creat_time' => time()
		);
		$result = $this->db()->insert('server', $insertArray);
		if($result == 0) return false;
		return $this->db()->insertId();
	}

	public function get($value, $type = 'server_id'){
		$whereArray = array(
			'server_id' => " server_id = '{$value}' ",
			'ip' => " ip = '{$value}' "
		);
		$sql = "SELECT * FROM server WHERE {$whereArray[$type]}";
		return $this->db()->query($sql, 'row');
	}

	public function updateInfo($server_id){
		$server = $this->get($server_id);

		$info = array();

		//系统描述
		$info['sys_descr'] = $this->format(snmprealwalk($server['ip'], $server['snmpv2_community'], 'system.sysDescr.0'));
		//连续开机时间
		$info['sys_uptime'] = $this->format(snmprealwalk($server['ip'], $server['snmpv2_community'], 'system.sysUpTime.0')); 
		//系统名称
		$info['sys_name'] = $this->format(snmprealwalk($server['ip'], $server['snmpv2_community'], 'system.sysName.0'));

		$this->db()->update('server', $info, "server_id = '{$server_id}'");
		return $info;
	}

	public function listGet($where = ''){
		if(!empty($where)) $where = " WHERE {$where}";
		$sql = "SELECT * FROM server {$where}";
		return $this->db()->query($sql, 'array');
	}

	public function diskGet($server_id, $name, $time_unit, $start_time, $stop_time){
		$start_year = date('Y', $start_time);
		$stop_year = date('Y', $stop_time);
		$year = array();
		for ($i = $start_year; $i <= $stop_year; $i++){
			$sql = "SHOW TABLES LIKE 'server_disk_log_{$i}'";
			$result = $this->db()->query($sql, 'row');
			if(!empty($result)) $year[] = $i;
		}
		$typeArray = array(
			'year' => '%Y',
			'month' => '%Y-%m',
			'day' => '%Y-%m-%d'
		);
		$sqltotal = array();
		foreach ($year as $key => $value) {
			$sqltotal[] = "SELECT count(server_disk_id) as log_count, date_format(FROM_UNIXTIME(insert_time),'{$typeArray[$time_unit]}') AS time, AVG(used/total) AS used_percent FROM server_disk_log_{$value} WHERE name = '{$name}'  AND insert_time >= {$start_time} AND insert_time <= {$stop_time} AND server_id = {$server_id} GROUP BY time";
		}
		$sqltotal = implode(' UNION ALL ', $sqltotal);
		$sqltotal .= ' ORDER BY time ASC';
		$arraytotal =  $this->db()->query($sqltotal, 'array');
		foreach ($arraytotal as $key => $value){
			$result[$value['time']] = $value;
		}
		return $arraytotal;
	}

	public function networkGet($server_id, $descr, $time_unit, $start_time, $stop_time){
		$start_year = date('Y', $start_time);
		$stop_year = date('Y', $stop_time);
		$year = array();
		for ($i = $start_year; $i <= $stop_year; $i++){
			$sql = "SHOW TABLES LIKE 'server_network_log_{$i}'";
			$result = $this->db()->query($sql, 'row');
			if(!empty($result)) $year[] = $i;
		}
		$typeArray = array(
			'year' => '%Y',
			'month' => '%Y-%m',
			'day' => '%Y-%m-%d'
		);
		$sqltotal = array();
		foreach ($year as $key => $value) {
			$sqltotal[] = "SELECT count(server_network_id) as log_count, date_format(FROM_UNIXTIME(insert_time),'{$typeArray[$time_unit]}') AS time, AVG(inOctets) AS in_avg, AVG(outOctets) AS out_avg FROM server_network_log_{$value} WHERE descr = '{$descr}'  AND insert_time >= {$start_time} AND insert_time <= {$stop_time} AND server_id = {$server_id}  GROUP BY time";
		}
		$sqltotal = implode(' UNION ALL ', $sqltotal);
		$sqltotal .= ' ORDER BY time ASC';
		$arraytotal =  $this->db()->query($sqltotal, 'array');
		foreach ($arraytotal as $key => $value){
			$result[$value['time']] = $value;
		}
		return $arraytotal;	
	}

	public function networkSummary($server_id, $descr, $start_time, $stop_time){
		$start_year = date('Y', $start_time);
		$stop_year = date('Y', $stop_time);
		$year = array();
		for ($i = $start_year; $i <= $stop_year; $i++){
			$sql = "SHOW TABLES LIKE 'server_network_log_{$i}'";
			$result = $this->db()->query($sql, 'row');
			if(!empty($result)) $year[] = $i;
		}
		$sqltotal = array();
		foreach ($year as $key => $value) {
			$sqltotal[] = "SELECT MAX(outOctets) AS out_max, MAX(inOctets) AS in_max, AVG(inOctets) AS in_AVG, AVG(outOctets) AS out_avg FROM server_network_log_{$value} WHERE descr = '{$descr}'  AND insert_time >= {$start_time} AND insert_time <= {$stop_time} AND server_id = {$server_id}";
		}
		$sqltotal = implode(' UNION ALL ', $sqltotal);
		//echo $sqltotal;
		$arraytotal =  $this->db()->query($sqltotal, 'row');
		return $arraytotal;	
	}

	public function memoryGet($server_id, $time_unit, $start_time, $stop_time){
		$start_year = date('Y', $start_time);
		$stop_year = date('Y', $stop_time);
		$year = array();
		for ($i = $start_year; $i <= $stop_year; $i++){
			$sql = "SHOW TABLES LIKE 'server_log_{$i}'";
			$result = $this->db()->query($sql, 'row');
			if(!empty($result)) $year[] = $i;
		}
		$typeArray = array(
			'year' => '%Y',
			'month' => '%Y-%m',
			'day' => '%Y-%m-%d'
		);
		$sqltotal = array();
		foreach ($year as $key => $value) {
			$sqltotal[] = "SELECT count(server_id) as log_count, date_format(FROM_UNIXTIME(insert_time),'{$typeArray[$time_unit]}') AS time, AVG(memory_used/memory_total) AS used_percent, AVG(virtual_memory_used/virtual_memory_total) AS virtual_used_percent, AVG(swap_memory_used/swap_memory_total) AS swap_used_percent FROM server_log_{$value} WHERE insert_time >= {$start_time} AND insert_time <= {$stop_time} AND server_id = {$server_id}  GROUP BY time";
		}
		$sqltotal = implode(' UNION ALL ', $sqltotal);
		$sqltotal .= ' ORDER BY time ASC';
		$arraytotal =  $this->db()->query($sqltotal, 'array');
		foreach ($arraytotal as $key => $value){
			$result[$value['time']] = $value;
		}
		return $arraytotal;			
	}

	public function cpuGet($server_id, $time_unit, $start_time, $stop_time){
		$start_year = date('Y', $start_time);
		$stop_year = date('Y', $stop_time);
		$year = array();
		for ($i = $start_year; $i <= $stop_year; $i++){
			$sql = "SHOW TABLES LIKE 'server_log_{$i}'";
			$result = $this->db()->query($sql, 'row');
			if(!empty($result)) $year[] = $i;
		}
		$typeArray = array(
			'year' => '%Y',
			'month' => '%Y-%m',
			'day' => '%Y-%m-%d'
		);
		$sqltotal = array();
		foreach ($year as $key => $value) {
			$sqltotal[] = "SELECT count(server_id) as log_count, date_format(FROM_UNIXTIME(insert_time),'{$typeArray[$time_unit]}') AS time, AVG(processor_load) AS processor_load_avg FROM server_log_{$value} WHERE insert_time >= {$start_time} AND insert_time <= {$stop_time} AND server_id = {$server_id}  GROUP BY time";
		}
		$sqltotal = implode(' UNION ALL ', $sqltotal);
		$sqltotal .= ' ORDER BY time ASC';
		$arraytotal =  $this->db()->query($sqltotal, 'array');
		foreach ($arraytotal as $key => $value){
			$result[$value['time']] = $value;
		}
		return $arraytotal;			
	}

	public function netstatGet($server_id, $time_unit, $start_time, $stop_time){
		$start_year = date('Y', $start_time);
		$stop_year = date('Y', $stop_time);
		$year = array();
		for ($i = $start_year; $i <= $stop_year; $i++){
			$sql = "SHOW TABLES LIKE 'server_log_{$i}'";
			$result = $this->db()->query($sql, 'row');
			if(!empty($result)) $year[] = $i;
		}
		$typeArray = array(
			'year' => '%Y',
			'month' => '%Y-%m',
			'day' => '%Y-%m-%d'
		);
		$sqltotal = array();
		foreach ($year as $key => $value) {
			$sqltotal[] = "SELECT count(server_id) as log_count, date_format(FROM_UNIXTIME(insert_time),'{$typeArray[$time_unit]}') AS time, AVG(netstat_count) AS netstat_avg FROM server_log_{$value} WHERE insert_time >= {$start_time} AND insert_time <= {$stop_time} AND server_id = {$server_id}  GROUP BY time";
		}
		$sqltotal = implode(' UNION ALL ', $sqltotal);
		$sqltotal .= ' ORDER BY time ASC';
		$arraytotal =  $this->db()->query($sqltotal, 'array');
		foreach ($arraytotal as $key => $value){
			$result[$value['time']] = $value;
		}
		return $arraytotal;			
	}

	public function runGet($server_id, $time_unit, $start_time, $stop_time){
		$start_year = date('Y', $start_time);
		$stop_year = date('Y', $stop_time);
		$year = array();
		for ($i = $start_year; $i <= $stop_year; $i++){
			$sql = "SHOW TABLES LIKE 'server_log_{$i}'";
			$result = $this->db()->query($sql, 'row');
			if(!empty($result)) $year[] = $i;
		}
		$typeArray = array(
			'year' => '%Y',
			'month' => '%Y-%m',
			'day' => '%Y-%m-%d'
		);
		$sqltotal = array();
		foreach ($year as $key => $value) {
			$sqltotal[] = "SELECT count(server_id) as log_count, date_format(FROM_UNIXTIME(insert_time),'{$typeArray[$time_unit]}') AS time, AVG(run_count) AS run_avg FROM server_log_{$value} WHERE insert_time >= {$start_time} AND insert_time <= {$stop_time} AND server_id = {$server_id}  GROUP BY time";
		}
		$sqltotal = implode(' UNION ALL ', $sqltotal);
		$sqltotal .= ' ORDER BY time ASC';
		$arraytotal =  $this->db()->query($sqltotal, 'array');
		foreach ($arraytotal as $key => $value){
			$result[$value['time']] = $value;
		}
		return $arraytotal;		
	}

	public function check($server_id){
		$table = $this->table();
		$server = $this->get($server_id);
		$ip = $server['ip'];
		$community = $server['snmpv2_community'];

		//get netstat
		$netstat = $this->netstat($ip, $community);
		$netstat_count = count($netstat);

		//run
		$run = $this->run($ip, $community);
		$run_count = count($run);

		//device
		$device = $this->device($ip, $community);

		//memory
		$memory = $this->memory($ip, $community);

		//cpu
		$cpu = $this->cpu($ip, $community);
		$processor_load = 0;
		if(count($cpu) > 0){
			$processor_load = $processor_load / count($cpu);
			foreach ($cpu as $key => $value) {
				$processor_load += (int)$value;
			}
		}

		$insertArray = array(
			'server_id' => $server_id,
			'insert_time' => time(),
			'netstat_count' => $netstat_count,
			'run_count' => $run_count,
			'processor_load' => $processor_load
		);
		if($memory['total'] !== false){
			$insertArray['memory_total'] = (int)$memory['total'];
		}
		if($memory['used'] !== false){
			$insertArray['memory_used'] = (int)$memory['used'];
		}
		if($memory['virtual'] !== false){
			$insertArray['virtual_memory_total'] = (int)$memory['virtual']['total'];
			$insertArray['virtual_memory_used'] = (int)$memory['virtual']['used'];
		}
		if($memory['swap'] !== false){
			$insertArray['swap_memory_total'] = (int)$memory['swap']['total'];
			$insertArray['swap_memory_used'] = (int)$memory['swap']['used'];
		}
		$result = $this->db()->insert($table, $insertArray);
		$log_id = $this->db()->insertId();

		//disk
		if($log_id > 0){
			$disk_table = $this->disk_table();
			$disk = $this->disk($ip, $community);
			foreach ($disk as $key => $value) {
				$insertArray = array(
					'log_id' => $log_id,
					'insert_time' => time(),
					'name' => base64_encode($value['name']),
					'total' => (int)$value['total'],
					'used' => (int)$value['used'],
					'server_id' => $server_id
				);
				$this->db()->insert($disk_table, $insertArray);
			}
		}

		//network
		if($log_id > 0){
			$network_table = $this->network_table();
			$network = $this->network($ip, $community);
			foreach ($network as $key => $value) {
				$insertArray = array(
					'log_id' => $log_id,
					'insert_time' => time(),
					'descr' => base64_encode($value['descr']),
					'type' => base64_encode($value['type']),
					'inOctets' => $value['inOctets'],
					'outOctets' => $value['outOctets'],
					'server_id' => $server_id
				);
				$this->db()->insert($network_table, $insertArray);
			}
		}

		$updateArray = array('last_watch_time' => time());
		if(!empty($netstat)) $updateArray['last_netstat'] = jencode($netstat);
		if(!empty($device)) $updateArray['last_device'] = jencode($device);
		if(!empty($cpu)) $updateArray['last_cpu'] = jencode($cpu);
		if(!empty($memory)) $updateArray['last_memory'] = jencode($memory);
		if(!empty($disk)) $updateArray['last_disk'] = jencode($disk);
		if(!empty($network)) $updateArray['last_network'] = jencode($network);
		$this->db()->update('server', $updateArray, "server_id = '{$server_id}'");

	}

	private function netstat($ip, $community){
		$result = snmprealwalk($ip, $community, '1.3.6.1.2.1.6.13.1.1');
		$return = array();
		foreach($result as $key => $value){
			$name = explode('.', $key);
			$return[] = array(
				$name[0],
				"{$name[1]}.{$name[2]}.{$name[3]}.{$name[4]}",
				$name[5],
				"{$name[6]}.{$name[7]}.{$name[8]}.{$name[9]}",
				$name[10],
				str_replace('INTEGER: ', '', $value)
			);
		}
		return $return;		
	}

	private function run($ip, $community){
		$run = array();
		$result = snmprealwalk($ip, $community, '1.3.6.1.2.1.25.4');
		$performance = snmprealwalk($ip, $community, '1.3.6.1.2.1.25.5');
		if(isset($result['HOST-RESOURCES-MIB::hrSWOSIndex.0'])) unset($result['HOST-RESOURCES-MIB::hrSWOSIndex.0']);
		foreach($result as $key => $value){
			if(!strstr($key, 'hrSWRunIndex')) break;
			$id = $this->format($value);
			$run[] = array(
				'name' => $this->format($result["HOST-RESOURCES-MIB::hrSWRunName.{$id}"]),
				'path' => $this->format($result["HOST-RESOURCES-MIB::hrSWRunPath.{$id}"]),
				'parameter' => $this->format($result["HOST-RESOURCES-MIB::hrSWRunParameters.{$id}"]),
				'type' => $this->format($result["HOST-RESOURCES-MIB::hrSWRunType.{$id}"]),
				'status' => $this->format($result["HOST-RESOURCES-MIB::hrSWRunStatus.{$id}"]),
				'cpu' => $this->format($performance["HOST-RESOURCES-MIB::hrSWRunPerfCPU.{$id}"]),
				'memory' => $this->format($performance["HOST-RESOURCES-MIB::hrSWRunPerfMem.{$id}"])
			);
		}
		return $run;
	}

	private function device($ip, $community){
		$device = array();
		$result = snmprealwalk($ip, $community, '1.3.6.1.2.1.25.3');
		foreach($result as $key => $value){
			if(!strstr($key, 'hrDeviceIndex')) break;
			$id = $this->format($value);
			$device[] = array(
				'type' => $this->format($result["HOST-RESOURCES-MIB::hrDeviceType.{$id}"]),
				'description' => $this->format($result["HOST-RESOURCES-MIB::hrDeviceDescr.{$id}"]),
			);
			
		}
		return $device;
	}

	private function memory($ip, $community){
		$memory = array('total' => false, 'used' => false, 'virtual' => false, 'swap' => false);
		$memory['total'] = $this->format(snmprealwalk($ip, $community, '1.3.6.1.2.1.25.2.2.0'));
		$result = snmprealwalk($ip, $community, '1.3.6.1.2.1.25.2');
		if(empty($result)) return false;
		foreach($result as $key => $value){
			if($label = strstr($key , 'hrStorageDescr')){
				//echo $this->format($value); 
				$label = explode('.', $label);
				$label = $label[1];
				if(strcasecmp($this->format($value), 'Virtual Memory') == 0 && ($size = $this->format($result["HOST-RESOURCES-MIB::hrStorageSize.{$label}"])) != 0){
					$memory['virtual'] = array(
						'used' => $this->format($result["HOST-RESOURCES-MIB::hrStorageUsed.{$label}"]),
						'total' => $size,
					);
				}
				if(strcasecmp($this->format($value), 'Physical Memory') == 0 && ($size = $this->format($result["HOST-RESOURCES-MIB::hrStorageSize.{$label}"])) != 0){	
					$memory['used'] = $this->format($result["HOST-RESOURCES-MIB::hrStorageUsed.{$label}"]); 
				}
				if(strcasecmp($this->format($value), 'Swap space') == 0 && ($size = $this->format($result["HOST-RESOURCES-MIB::hrStorageSize.{$label}"])) != 0){
					$memory['swap'] = array(
						'used' => $this->format($result["HOST-RESOURCES-MIB::hrStorageUsed.{$label}"]),
						'total' => $size
					);

				}

			}
		}
		return $memory;
	}

	private function cpu($ip, $community){
		$cpu = array();
		$result = snmprealwalk($ip, $community, '1.3.6.1.2.1.25.3.3.1.2');
		foreach($result as $value){
			$cpu[] = $this->format($value);
		}
		return $cpu;	
	}

	private function disk($ip, $community){
		$disk = array();
		$result = snmprealwalk($ip, $community, '1.3.6.1.2.1.25.2');
		foreach($result as $key => $value){
			if($label = strstr($key , 'hrStorageDescr')){
				$label = explode('.', $label);
				$label = $label[1];
				if(($name = strstr($value, '/')) || strstr($value, '\\')){
					if($name === false) $name = $this->format($value);
					if(($size = $this->format($result["HOST-RESOURCES-MIB::hrStorageSize.{$label}"])) != 0){
						$disk[] = array(
							'name' => $name,
							'total' => $size,
							'used' => $this->format($result["HOST-RESOURCES-MIB::hrStorageUsed.{$label}"]) 
						);
					}
				}
			}
		}
		return $disk;
	}

	private function network($ip, $community){
		$network = array();
		$result = snmprealwalk($ip, $community, '1.3.6.1.2.1.2.2.1');
		foreach($result as $key => $value){
			if($label = strstr($key , 'ifIndex')){
				$index = (int)$this->format($value);
				$network[] = array(
					'descr' => $this->format($result["IF-MIB::ifDescr.{$index}"]),
					'type' => $this->format($result["IF-MIB::ifType.{$index}"]),
					'mtu' => $this->format($result["IF-MIB::ifMtu.{$index}"]),
					'speed' => $this->format($result["IF-MIB::ifSpeed.{$index}"]),
					'physAddress' => $this->format($result["IF-MIB::ifPhysAddress.{$index}"]),
					'adminStatus' => $this->format($result["IF-MIB::ifAdminStatus.{$index}"]),
					'operStatus' => $this->format($result["IF-MIB::ifOperStatus.{$index}"]),
					'inOctets' => $this->format($result["IF-MIB::ifInOctets.{$index}"]),
					'inUcastPkts' => $this->format($result["IF-MIB::ifInUcastPkts.{$index}"]),
					'inNUcastPkts' => $this->format($result["IF-MIB::ifInNUcastPkts.{$index}"]),
					'inErrors' => $this->format($result["IF-MIB::ifInErrors.{$index}"]),
					'inUnknownProtos' => $this->format($result["IF-MIB::ifInUnknownProtos.{$index}"]),
					'outOctets' => $this->format($result["IF-MIB::ifOutOctets.{$index}"]),
					'outUcastPkts' => $this->format($result["IF-MIB::ifOutUcastPkts.{$index}"]),
					'outNUcastPkts' => $this->format($result["IF-MIB::ifOutNUcastPkts.{$index}"]),
					'outErrors' => $this->format($result["IF-MIB::ifOutErrors.{$index}"]),
					'outQLen' => $this->format($result["IF-MIB::ifOutQLen.{$index}"]),
				);
			}
		}
		return $network;	
	}

	private function format($result){
		if(!$result) return false;
		if(is_array($result)) $result = array_shift($result);
		$result = str_replace(array('STRING: ','INTEGER: ','Counter32: ','Gauge32: '),'', $result);
		$result = preg_replace('/^"(.*)"$/', '$1', $result);
		return $result;
	}

	private function table(){		//判断表是否存在,不存在的话,创建。返回表名。
		$year = date('Y');
		$name = "server_log_{$year}";
		$sql = "CREATE TABLE IF NOT EXISTS `{$name}` (
				  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `server_id` int(10) unsigned NOT NULL,
				  `run_count` int(10) unsigned NOT NULL,
				  `netstat_count` int(10) unsigned NOT NULL,
				  `memory_total` bigint(20) unsigned NOT NULL,
				  `memory_used` bigint(20) unsigned NOT NULL,
				  `virtual_memory_total` bigint(20) unsigned NOT NULL,
				  `virtual_memory_used` bigint(20) unsigned NOT NULL,
				  `swap_memory_total` bigint(20) unsigned NOT NULL,
				  `swap_memory_used` bigint(20) unsigned NOT NULL,
				  `processor_load` int(10) unsigned NOT NULL,
				  `insert_time` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`log_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$this->db()->query($sql, 'exec');
		return $name;
	}

	private function disk_table(){		//判断表是否存在,不存在的话,创建。返回表名。
		$year = date('Y');
		$name = "server_disk_log_{$year}";
		$sql = "CREATE TABLE IF NOT EXISTS `{$name}` (
					`server_disk_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`server_id` int(10) unsigned NOT NULL,
					`log_id` int(10) unsigned NOT NULL,
					`name` varchar(255) NOT NULL,
					`total` bigint(20) unsigned NOT NULL,
					`used` bigint(20) unsigned NOT NULL,
					`insert_time` int(10) unsigned NOT NULL,
					PRIMARY KEY (`server_disk_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$this->db()->query($sql, 'exec');
		return $name;
	}

	private function network_table(){		//判断表是否存在,不存在的话,创建。返回表名。
		$year = date('Y');
		$name = "server_network_log_{$year}";
		$sql = "CREATE TABLE IF NOT EXISTS `{$name}` (
					`server_network_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`server_id` int(10) unsigned NOT NULL,
					`log_id` int(10) unsigned NOT NULL,
					`descr` varchar(255) NOT NULL,
					`type` varchar(255) NOT NULL,
					`inOctets` bigint(20) unsigned NOT NULL,
					`outOctets` bigint(20) unsigned NOT NULL,
					`insert_time` int(10) unsigned NOT NULL,
					PRIMARY KEY (`server_network_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$this->db()->query($sql, 'exec');
		return $name;
	}

}
?>
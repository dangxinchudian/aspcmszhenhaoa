<?php
class aws extends modelLog{

	public function daily($domain, $start_time, $stop_time){
		$schema = str_replace('.', '_', $domain).'_log';
		$check = $this->checkSchema($schema);
		if(!$check) return false;
		$start_time = date('Ymd', $start_time);
		$stop_time = date('Ymd', $stop_time);
		$sql = "SELECT * FROM {$schema}.daily WHERE daily.day >= {$start_time} AND daily.day <= {$stop_time}";
		return $this->db()->query($sql, 'array');
	}

	public function checkSchema($schema){
		$sql = "SELECT * FROM information_schema.TABLES WHERE table_schema='{$schema}'";
		$result = $this->db()->query($sql,'row');
		if(empty($result)) return false;
		return true;
	}

	public function browser($domain, $time = 0){
		$schema = str_replace('.', '_', $domain).'_log';
		$check = $this->checkSchema($schema);
		if(!$check) return false;
		$time = date('Ym', $time);
		if($time != 0){		//if one month
			$sql = "SELECT * FROM {$schema}.browser WHERE browser.year_month = '{$time}' ORDER BY browser.hits DESC";
		}else{
			$sql = "SELECT * FROM {$schema}.browser GROUP BY browser.name ORDER BY browser.hits DESC";
		}
		return $this->db()->query($sql, 'array');
	}

	public function os($domain, $time = 0){
		$schema = str_replace('.', '_', $domain).'_log';
		$check = $this->checkSchema($schema);
		if(!$check) return false;
		$time = date('Ym', $time);
		if($time != 0){		//if one month
			$sql = "SELECT * FROM {$schema}.os WHERE os.year_month = '{$time}' ORDER BY os.hits DESC";
		}else{
			$sql = "SELECT * FROM {$schema}.os GROUP BY os.name ORDER BY os.hits DESC";
		}
		return $this->db()->query($sql, 'array');
	}

	public function general($domain, $start_time, $stop_time){
		$schema = str_replace('.', '_', $domain).'_log';
		$check = $this->checkSchema($schema);
		if(!$check) return false;
		$start_time = date('Ym', $start_time);
		$stop_time = date('Ym', $stop_time);
		$sql = "SELECT * FROM {$schema}.general WHERE general.year_month >= '{$start_time}' AND general.year_month <= '{$stop_time}'";
		return $this->db()->query($sql, 'array');		
	}

	public function summary($array){
		if(empty($array)) return false;
		$return = array();
		$field = array();
		$ignore = array('day', 'year_month');
		foreach ($array[0] as $key => $value) {
			if(!in_array($key, $ignore)) $field[] = $key;
		}
		foreach ($array as $key => $value) {
			foreach ($field as $fvalue) {
				if(isset($return["sum_{$fvalue}"])) $return["sum_{$fvalue}"] = $value[$fvalue] + $return["sum_{$fvalue}"];
				else $return["sum_{$fvalue}"] = $value[$fvalue];
				if(isset($return["max_{$fvalue}"])){
					if($value[$fvalue] > $return["max_{$fvalue}"]) $return["max_{$fvalue}"] = $value[$fvalue];
				}else $return["max_{$fvalue}"] = $value[$fvalue];
				if(isset($return["min_{$fvalue}"])){
					if($value[$fvalue] < $return["min_{$fvalue}"]) $return["min_{$fvalue}"] = $value[$fvalue];
				}else $return["min_{$fvalue}"] = $value[$fvalue];
			}
		}
		foreach ($field as $value) {
			$return["avg_{$value}"] = $return["sum_{$value}"] / count($array);
		}
		return $return;
	}

}
?>
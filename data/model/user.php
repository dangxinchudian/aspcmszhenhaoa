<?php
class user extends model{

	public function sessionCheck($callback = false){
		if(empty($_SESSION['user_id'])){
			if($callback) return $callback();
			else exit(header('Location: ./'));
		}else return $_SESSION['user_id'];
	}

	public function creat($mail, $pass){
		$salt = random('str', 27);
		$passwd = $this->passEncode($pass, $salt);
		$insertArray = array(
			'mail' => $mail, 
			'usalt' => $salt, 
			'passwd' => $passwd,
			'creat_time' => time()
		);
		$result = $this->db()->insert('user', $insertArray);
		if($result == 0) return false;
		return $this->db()->insertId();
	}

	public function passEncode($pass, $salt){
		return md5($salt.'?'.$salt.'='.$pass);
	}

	public function get($value, $type = 'user_id'){
		$whereArray = array(
			'user_id' => " user_id = '{$value}' ",
			'mail' => " mail = '{$value}' "
		);
		$sql = "SELECT * FROM user WHERE {$whereArray[$type]}";
		return $this->db()->query($sql, 'row');
	}

	public function login($user_id){
		$_SESSION['user_id'] = $user_id;
		$updateArray = array('last_login_time' => time());
		$result = $this->db()->update('user', $updateArray, "user_id = '{$user_id}'");
		if($result > 0) return true;
		return false;
	}

}
?>
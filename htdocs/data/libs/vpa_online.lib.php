<?php

class vpa_online {
	public $id;
	public $online_time;

	public function vpa_online($id = null, $lifetime = 300) {
		$this->id = $id;
		$this->online_time = $lifetime;
	}

	public function set_time() {
		$o_u = new VPA_table_users;
		$o_u->set($ret, array('ldate' => time()), $this->id);
	}

	public function get_online() {
		$o_u = new VPA_table_users;
		$o_u->get($ret, array('id' => $this->id, 'ldate' => time() - $this->online_time), null, 0, 1);
		$ret->get_first($ldate);
		return (!empty($ldate));
	}

	public function cache_get_online() {
		if (is_array($this->id) && isset($this->id['ldate'])) {
			return ($this->id['ldate'] >= time() - $this->online_time);
		}
		return false;
	}

	/**
	 * @deprecated
	 */
	public function get_time() {
		return file_exists(ONLINE_CACHE . '/' . $this->id) ? filemtime (ONLINE_CACHE . '/' . $this->id) : 0;
	}

	public function get_online_users($order = null) {
		$o_u = new VPA_table_users;
		$o_u->get_params($ret, array('ldate' => time() - $this->online_time), $order, null, null, null, array('id', 'nick', 'avatara', 'rating'));
		$ret->get($online_users);
		return $online_users;
	}

	public function get_count_online_users() {
		$o_u = new VPA_table_users;
		$o_u->get_num($ret, array('ldate' => time() - $this->online_time));
		$ret->get_first($ret);
		return $ret['count'];
	}
}

?>

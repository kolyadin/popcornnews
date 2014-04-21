<?php
class session {
	public $logs;
	public $session_name;
	public $session_id;

	public function &getInstance() {
		static $instance;
		if (!isset($instance)) $instance = new session;
		return $instance;
	}

	public function session($name = "SESSID") {
		$this->log = &VPA_logger::getInstance();
		$this->session_name = $name;
		$this->session_id = null;
	}

	public function start() {
		$status = 1;
		$start = $this->log->get_time();
		if (empty($this->session_id)) {
			session_name($this->session_name);
			session_start();
			$cookie_path = dirname($_SERVER['REQUEST_URI']);
			$cookie_domain = trim($cookie_path, "\/") . $_SERVER['HTTP_HOST'];
			session_set_cookie_params (SESSION_LIFETIME);
			$this->session_id = addslashes(session_id());
			$this->log->add_message(get_class($this), "Session start(" . SESSION_LIFETIME . ") <br><pre>" . print_r ($_SESSION, true) . "</pre>", $start, $status);
			return true;
		}
		$this->log->add_message(get_class($this), "Session уже запущена (" . SESSION_LIFETIME . ") <br><pre>" . print_r ($_SESSION, true) . "</pre>", $start, $status);
	}

	public function save_var($name, $value) {
		$status = 1;
		$start = $this->log->get_time();
		$_SESSION["__session_var__" . $name] = $value;
		{
			ob_start();
			echo "<pre>";
			print_r ($_SESSION["__session_var__" . $name]);
			echo "</pre>";
			$results = ob_get_contents();
			ob_end_clean();
			$this->log->add_message(get_class($this), "Save public $name: $results", $start, $status);
		}
		return $status;
	}

	public function save_user_var($name, $value) {
		$status = 1;
		$start = $this->log->get_time();
		$_SESSION['__session_var__sess_user'][$name] = $value;
		{
			ob_start();
			echo "<pre>";
			print_r ($_SESSION['__session_var__sess_user'][$name]);
			echo "</pre>";
			$results = ob_get_contents();
			ob_end_clean();
			$this->log->add_message(get_class($this), "Save public $name: $results", $start, $status);
		}
		return $status;
	}

	public function delete_var($name) {
		$status = 1;
		$start = $this->log->get_time();
		unset ($_SESSION["__session_var__" . $name]);
		$status = session_unregister("__session_var__" . $name);
		$this->log->add_message(get_class($this), "Delete var <b>$name</b>", $start, $status);
		return $status;
	}

	public function restore_var($name) {
		return isset($_SESSION["__session_var__" . $name]) ? $_SESSION["__session_var__" . $name] : null;
	}

	/**
	 * Restore var and return in link, if exist
	 *
	 * @param string $name - name of var
	 * @return link to var
	 */
	public function &restore_link_to_var($name) {
		return $_SESSION['__session_var__' . $name];
	}

	public function restore_user_var($name) {
		return isset($_SESSION['__session_var__sess_user'][$name]) ? $_SESSION['__session_var__sess_user'][$name] : null;
	}

	public function end() {
		$status = 1;
		$start = $this->log->get_time();
		$status = session_destroy();
		$this->log->add_message(get_class($this), "End of session", $start, $status);
		return $status;
	}
}

?>
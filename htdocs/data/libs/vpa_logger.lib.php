<?php

class VPA_logger {
	static public function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = (DEBUG && VPA_template::isDeveloper()) ? new VPA_logger_old : new VPA_logger_empty;
		}
		return $instance;
	}
}

class VPA_logger_empty {
	public function __call($name, $arguments) {}
}

class VPA_logger_old {
	public $types;
	public $logs;
	public $list;
	public $root;
	public $i;

	static public function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new VPA_logger;
		}
		return $instance;
	}

	public function get_time() {
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}

	public function init($type, $name) {
		$this->types[$type] = $name;
		if (!isset($this->logs[$type])) {
			$this->logs[$type] = array();
		}
	}

	public function add_message($type, $message, $start, $status) {
		$end = $this->get_time();
		$this->logs[$type][] = array('message' => $message, 'start' => $start, 'end' => $end, 'status' => $status);
	}

	public function show($tree = false) {
		$str = "";
		$strip_logs = array('vpa_table', 'vpa_field', 'vpa_sql_quark', 'vpa_db_drivers_factory');
		if (!$tree) {
			foreach ($this->logs as $indx => $log) {
				$show = true;
				foreach ($strip_logs as $strip) {
					$pos = strpos(strtolower($indx), $strip);
					if ($pos !== false) {
						$show = false;
						break;
					}
				}
				if ($show) {
					$str .= "<table border=1 width=90%>";
					$str .= "<tr><td colspan=5><b>$indx</b></td></tr>";
					$str .= "<tr><td>N</td><td>Execution Time</td><td width=90%>Message</td><td>Status</td></tr>";
					foreach ($log as $num => $record) {
						$time = number_format($record['end'] - $record['start'], 4);
						$fon = $record['status'] ? "style='background-color:#ddffdd;'" : "style='background-color:#ffdddd;'";
						$str .= "<tr $fon><td>" . ($num + 1) . "</td><td>" . $time . "</td><td>" . $record['message'] . "</td><td>" . ($record['status'] ? "true" : "false") . "</td></tr>";
					}
					$str .= "</table>";
				}
			}
		} else {
			$this->make_tree();
			$this->i = 1;
			$str .= "<script>

		  var _vpa_log_title=document.createElement('div');
		  var _vpa_log_title_timer=null;
		  document.body.appendChild(_vpa_log_title);

		  function _vpa_log_tree(obj,cl)
		  {
			  if (obj.src.indexOf('MP.gif')>=0)
			  {
				  obj.src=obj.src.replace(/MP.gif/i,'MM.gif');
				  _vpa_log_hide(cl);
			  }
			  else
			  {
				  obj.src=obj.src.replace(/MM.gif/i,'MP.gif');
				  _vpa_log_show(cl);
			  }

		  }

		public function _vpa_log_hide(cid)
		{
			tb_obj=document.getElementById('log_tree');
			for(i=0;i<tb_obj.rows.length;i++)
			{
				tb_obj.rows[i].id.indexOf(cid+'_')>=0 && (document.getElementById(tb_obj.rows[i].id).style.display='none');
			}
		}

		public function _vpa_log_show(cid)
		{
			tb_obj=document.getElementById('log_tree');
			for(i=0;i<tb_obj.rows.length;i++)
			{
				tb_obj.rows[i].id.indexOf(cid+'_')>=0 && (document.getElementById(tb_obj.rows[i].id).style.display=(document.all && !document.opera) ? 'block' : 'table-row');
			}
		}


		public function _vpa_log_set_color(obj,color)
		{
			for (i=0;i<obj.cells.length;i++)
			{
				obj.cells[i].style.background=color;
				childs=obj.cells[i].getElementsByTagName('div');
				for (j=0;j<childs.length;j++)
				{
					childs[j].style.background=color;
				}
			}
		}

		public function _vpa_log_show_title(obj)
		{
			co=Array(0,0);
			co=_vpa_log_get_coords_obj(obj,co);
			x=co[0];
			y=co[1]+20;
			_vpa_log_title.innerHTML=obj.innerHTML;
			_vpa_log_title_timer=window.setTimeout(\"_vpa_log_title.style.cssText='position:absolute; top:'+y+'; left:'+x+'; display: block; border:1px solid #000; background-color:#eee; padding:5px; font-size:12px; font-family: arial;'\",500);
		}

		public function _vpa_log_hide_title(obj)
		{
			window.clearTimeout(_vpa_log_title_timer);
			_vpa_log_title.style.cssText='display: none;';
		}

		public function _vpa_log_get_coords_obj(obj,c)
		{
			c[0]+=obj.offsetLeft;
			c[1]+=obj.offsetTop;
			if (obj.offsetParent)
				return _vpa_log_get_coords_obj(obj.offsetParent,c);
			else
				return c;
		}

		  </script>";
			$str .= "<table id=log_tree width=99% align=center cellpadding=0 cellspacing=0 style=\"background-color:#555; border:1px solid #555;\">";
			$str .= "<tr style=\"background-color:#dddddd; font-size:12px; font-family: arial;\"><th>N</th><th>Time</th><th width=90%>Message</th><th>Status</th></tr>";
			$childs = array();
			$this->get_childs_for($childs, -2);
			$str .= $this->draw_tree('', -2, 0, '', count($childs));
			$str .= "</table>";
		}
		echo $str;
	}

	public function draw_tree($str = '', $parent_id = -2, $level = 0, $prefix = '', $last = 0, $class = 'id') {
		$childs = array();
		$this->get_childs_for($childs, $parent_id);
		$count = count($childs);

		$i = 0;
		foreach ($childs as $indx => $val) {
			$class2 = $class . '_' . $val['id'];
			$childs2 = array();
			$this->get_childs_for($childs2, $val['id']);
			$count2 = count($childs2);

			$shift = $prefix . (($count && $count-1 != $i) ? "<img src=" . WWW . "vpa_engine/imgs/MT.gif>" : "<img src=" . WWW . "vpa_engine/imgs/ML.gif>");

			if ($count2) {
				$shift .= "<img src=" . WWW . "vpa_engine/imgs/MP.gif onclick=\"_vpa_log_tree(this,'$class2');\">";
				$down = $prefix . (($count && $count-1 > $i) ? "<img src=" . WWW . "vpa_engine/imgs/MI.gif>" : "<img src=" . WWW . "vpa_engine/imgs/MB.gif>");
			} else {
				$shift .= "<img src=" . WWW . "vpa_engine/imgs/MS.gif>";
				$down = "";
			}
			$time = number_format($val['end'] - $val['start'], 4);
			$message = "<font color=#000099>" . $val['class'] . "</font>::" . $val['message'];
			$message = preg_replace("/\'(.*?)\'/is", "'<font color=#009900>\\1</font>'", $message);
			$message = preg_replace("/true/is", "<font color=#009900>true</font>", $message);
			$message = preg_replace("/false/is", "<font color=#990000>false</font>", $message);
			$message = "<div onmouseover='_vpa_log_show_title(this)' onmouseout='_vpa_log_hide_title(this);' style='background-color:#fff; width:100%; padding-top:5px;  float:left;'>&nbsp;" . $message . "</div>";

			$str .= "<tr id=\"$class2\" onmouseover=\"_vpa_log_set_color(this,'#DBE5EB');\" onmouseout=\"_vpa_log_set_color(this,'" . ($val['status'] ? "#FFF" : "#FEE") . "');\" style=\"background-color:#" . ($val['status'] ? "fff" : "ffeeee") . "; font-size:12px; font-family: arial;\"><td style='border-bottom:1px solid #ddd; border-right:1px solid #ddd;'>&nbsp;{$this->i}&nbsp;</td><td  style='border-bottom:1px solid #ddd; border-right:1px solid #ddd;'>&nbsp;$time&nbsp;</td><td><table cellpadding=0 cellspacing=0><tr><td>" . $shift . "</td><td style='font-size:12px;font-family: arial;'>$message</td></tr></table></td><td align=center style='border-bottom:1px solid #ddd; border-left:1px solid #ddd;'>&nbsp;" . ($val['status'] ? "true" : "false") . "&nbsp;</td></tr>\n";
			$in = $this->draw_tree('', $val['id'], $level + 1, $down, $count, $class2);
			$this->i = $this->i + 1;
			$i++;
			$str .= $in;
		}
		return $str;
	}

	public function make_tree() {
		$this->list = array();

		foreach ($this->logs as $indx => $log) {
			foreach ($log as $num => $record) {
				$log[$num]['class'] = $indx;
			}
			$this->list = array_merge($this->list, $log);
		}

		foreach ($this->list as $indx => $value) {
			$pr = $this->get_parent_for($indx, $value);
			$this->list[$indx]['parent'] = $pr;
			$this->list[$indx]['id'] = $indx;
		}
		$childs = array();
		$this->get_childs_for($childs, -1);
		$start = $end = 0;
		if (count($childs)) {
			$start = $childs[0]['start'];
			$end = $childs[count($childs)-1]['end'];
		}
		$this->list[$indx + 1] = array('parent' => -2, 'id' => -1, 'start' => $start, 'end' => $end, 'class' => '<b>Log tree</b>', 'message' => '<b>' . $_SERVER['HTTP_HOST'] . '</b>', 'status' => true);
	}

	public function get_min_time($ar) {
		$first = current($ar);
		$min_time = $first['start'];
		foreach ($ar as $indx => $val) {
			if ($val['start'] < $min_time) {
				$min_time = $val['start'];
			}
		}
		return $min_time;
	}

	public function get_childs_for(&$ret, $parent_id = -2) {
		foreach ($this->list as $indx => $val) {
			if ($val['parent'] == $parent_id) {
				$ret[] = $this->list[$indx];
			}
		}
	}

	public function get_parent_for($i, $obj) {
		$els = array();
		foreach ($this->list as $indx => $key) {
			if ($key['start'] <= $obj['start'] && $obj['end'] < $key['end']) {
				$els[$indx] = $key;
			}
		}

		if (count($els)) {
			$pr = key($els);
			$current = current($els);
			foreach ($els as $ei => $ek) {
				if ($ek['start'] >= $current['start'] && $ek['end'] <= $current['end']) {
					$current = $ek;
					$pr = $ei;
				}
			}
			return $pr;
		} else {
			return -1;
		}
	}

	public function serialize($var) {
		return serialize($var);
	}
}

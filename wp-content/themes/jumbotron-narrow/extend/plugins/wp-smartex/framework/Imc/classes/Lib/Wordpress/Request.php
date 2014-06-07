<?php
class Lib_Wordpress_Request {
	private $_requests = array ();
	protected $requested_with;
	public static $current;
	public function __construct($route, $action) {
		$classname = "Controller_" . ucfirst ( $route );
		$this->_requests [$classname] = new $classname ();
		$this->_requests [$classname]->setRequest($this);
		self::$current = $this;
		$action = "action_" . $action;
		if (method_exists ( $this->_requests [$classname], $action ))
			add_action ( "admin_menu", array (
					$this->_requests [$classname],
					$action 
			) );
	}
	public static function factory($route = "") {
		$default = "index";
		if (isset ( $_GET ["action"] ))
			$action = $_GET ["action"];
		elseif (isset ( $_POST ["action"] ))
			$action = $_POST ["action"];
		else
			$action = $default;
		return new Lib_Wordpress_Request ( $route, $action );
	}
	public function is_ajax() {
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
		{
			// Typically used to denote AJAX requests
			return true;
		}
		return false;
	}
	public function query($var) {
		if(isset($_GET[$var]))
			return $_GET[$var];
		return false;
	}
	public function uri($path=false) {
		if(!$path)
			return site_url();
		return site_url($path);
	}
	public function action() {
		if(isset($_GET["action"])) {
			return $_GET["action"];
		}
		if(isset($_POST["action"])) {
			return $_POST["action"];
		}
		return;
	}
	public static function current() {
		return self::$current;
	}
}
?>

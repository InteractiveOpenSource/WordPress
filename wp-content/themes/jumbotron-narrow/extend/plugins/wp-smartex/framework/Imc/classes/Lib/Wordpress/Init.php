<?php
class Lib_Wordpress_Init implements Interface_Init {

  private $_init;
  private static $_paths = array();

  public function __construct() {
    self::dirs(LITHIUM_PLUGIN_ROOT."/application/classes");
    self::dirs(LITHIUM_PLUGIN_ROOT."/classes");
    define("APPPATH", LITHIUM_PLUGIN_ROOT."/application/");
    define("DOCROOT", LITHIUM_PLUGIN_ROOT."/");
    spl_autoload_register(array("Lib_Wordpress_Init", "auto_load"));
    $this->_init = new Lib_Init;
    $this->run();
  }
  
  public function run() {
      $route = LITHIUM_PLUGIN_ROOT . "/application/config/route.php";

      $route = apply_filters('smartex_route', $route);
      do_action('smartex_load', $route);

      require_once $route;
      foreach (Lib_Wordpress_Route::all() as $route) {
	  	Lib_Wordpress_Request::factory($route);
      }
  }
  
  public static function dirs($root) {
      if(!is_dir($root))
	  return;
      if($root=="." || $root=="..")
	  return;
      $dh = opendir($root);
      while($f = readdir($dh)) {
	  if(is_dir($root . "/" . $f) && $f!="." && $f!="..")
	      self::dirs($root . "/" . $f);
	  elseif(preg_match("/classes\/(.+)\.php$/i", $root."/".$f, $ar)) {
	      if(isset($ar[1])) {
		  $r = explode("/", $ar[1]);
		  $classname = implode("_", $r);
		  if(!isset(self::$_paths[$classname]))
		    self::$_paths[$classname] = $root . "/" . $f;
	      }
	  }
      }
      closedir($dh);
  }

  public static function setup() {
    $id_index = wp_insert_post(array(
		"post_status" => "publish",
		"post_type" => "page",
		"post_author" => "Lithium",
		"guid" => "lithium",
		"post_title" => "lithium"
	    ));
    add_option("lithium", (object) array("index" => $id_index));
    $option = get_option("rewrite_rules");

    update_option("lithium", "");
  }

  public static function uninstall() {
    $lithium = get_option("lithium");
    wp_delete_post($lithium->index);
    delete_option("lithium");
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }

  public function menu() {
    add_pages_page("okok", "halala", "activate_plugins", "tssssle", array($this, "index"));
    add_menu_page("Un titre test", "titre", "activate_plugins", "han", array($this, "index"), "", 1);
  }

  public function pagy($pages) {
      
    foreach ($pages as $k_page => $page) {
      if ($page->post_name == "lithium") {
	$pages[$k_page] = NULL;
	unset($pages[$k_page]);
      }
    }
    return $pages;
  }

  public function index() {
    $option = get_option("rewrite_rules");
    var_dump($option);
  }

  public static function auto_load($class) {
      if(preg_match("/wp/i", $class))
	      return;
    try {
      if ($path = self::find_file('classes', $class)) {
	// Load the class file
	require $path;

	// Class has been found
	return TRUE;
      }

      // Class is not in the filesystem
      return FALSE;
    } catch (Exception $e) {
      self::handler($e);
      die;
    }
  }

  public static function find_file($directory, $file) {
    if (preg_match("/^_?WP_/e", $file))
      return FALSE;
    if (self::is_camelcase($file))
      return LITHIUM_PLUGIN_ROOT . "/" . $directory . "/Lib/Wordpress/" . $file . ".php";
    if(isset(self::$_paths[$file]))
	return self::$_paths[$file];
  }

  public static function handler($error) {
    return $error;
  }

  public static function is_camelcase($str) {
    if (strpos($str, "_") !== FALSE)
      return FALSE;
    return preg_match("/([A-Z]){2,}/e", $str);
  }

  public function rewrite_rules($rules) {
    return Lib_Wordpress_Route::fetch_routes() + $rules;
  }

  public function flush_rules() {
    $rules = get_option('rewrite_rules');
    $routes = Lib_Wordpress_Route::fetch_routes();
    if (count(array_intersect_key($rules, $routes)) < count($routes)) {
      global $wp_rewrite;
      $wp_rewrite->flush_rules();
    }
  }

  public function insert_query_vars($vars) {
    return $vars;
  }

}

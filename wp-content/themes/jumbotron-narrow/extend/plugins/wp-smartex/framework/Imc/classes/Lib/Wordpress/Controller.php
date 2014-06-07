<?php

/**
 * @param Lim $model
 * @param Lib_Wordpress_Response $response
 */
abstract class Lib_Wordpress_Controller {
	protected $model_name, $title, $model, $action;
	public $response;
	protected $request;
	/**
	 * 
	 * @var Twig_Environment
	 */
	private $twig;
	protected $template = "page";
	/**
	 * 
	 * @var Lib_Controller
	 */
	private $_controller;
	
	public function __construct($id = NULL) {
		$this->response = new Lib_Wordpress_Response ();
		
		$this->_controller = new Lib_Controller();
		
		Junten::$theme = "Admin";
		$map = "Map_" . ucfirst(Junten::$theme);
		Junten::$map = new $map();
		
		$user = "admin";
		$loader = new Twig_Loader_Filesystem();
		$loader->addPath(DOCROOT . "themes/$user/templates");
		$loader->addPath(APPPATH . "views/$user");
		$this->twig = new Twig_Environment($loader, array(
				"autoescape" => FALSE
		));
		$truncate = new Twig_SimpleFilter("truncate", array("JuntenSeo", "limit_chars"));
		$this->twig->addFilter($truncate);
		$translate = new Twig_SimpleFilter("translate", array("I18n", "get"));
		$this->twig->addFilter($translate);
		$rebase = new Twig_SimpleFilter("rebase", function($str) {
			return Junten::site(Junten::$theme . "/$str");
		});
		$this->twig->addFilter($rebase);
		
		$methods = get_class_methods ( get_class ( $this ) );		
		foreach ( $methods as $method ) {
			// reserver 'hook_' prefixed method to be called as action
			if (preg_match ( "/^hook_(.+)/", $method, $ar ))
				add_action ( $ar [1], array (
						$this,
						$method 
				) );
		}
	}
	/**
	 * 
	 * @param Lib_Wordpress_Request $request
	 */
	public function setRequest($request) {
		$this->request = $request;
	}
	public static function factory($model, $id = NULL) {
		$class = "Controller_" . $model;
		if (isset ( $id ))
			return new $class ( $id );
		return new $class ();
	}
	public function __get($bloc) {
		if (! isset ( $this->$bloc ) && method_exists ( $this, "get_" . $bloc )) {
			$method = "get_" . $bloc;
			$this->$bloc = $this->$method ();
			return $this->$bloc;
		}
		return $this->$bloc;
	}
	
	/**
	 * Stores a named route and returns it.
	 * The "action" will always be set to
	 * "index" if it is not defined.
	 *
	 * Route::set('default', '(<controller>(/<action>(/<id>)))')
	 * ->defaults(array(
	 * 'controller' => 'welcome',
	 * ));
	 *
	 * @param
	 *        	string route name
	 * @param
	 *        	string URI pattern
	 * @param
	 *        	array regex patterns for route keys
	 * @return Route
	 */
	public static function route($name, $uri_callback = NULL, $regex = NULL) {
		// return Lib_Wordpress_Route::get($name) = new Lib_Wordpress_Route($uri_callback, $regex);
	}
	
	/**
	 * ******************DEFAULT REWRITING ACTION FOR FRONT PAGES
	 */
	public function hook_rewrite_rules_array($rules) {
		$newrules = array ();
		// $newrules['^admin/impopup/(.+)$'] = 'index.php?pagename=page-d-exemple&action=$matches[1]';
		return $newrules + $rules;
	}
	public function hook_query_vars($vars) {
		// array_push($vars, 'action');
		return $vars;
	}
	public function hook_wp_loaded() {
		global $wp_rewrite;
		$rules = get_option ( 'rewrite_rules' );
		// if (!isset($rules['^admin/impopup/(.+)$'])) {
		// $wp_rewrite->flush_rules();
		// }
	}
	
	protected function after() {		
		if ($this->request->is_ajax())
			return;
		$content = array();
		Junten::$map->fill("messages", array(
		"><build" => array("", "alert/tri", array(
		"><method" => "message",
		"twig_test_open" => '<?php  if(count($messages)>0) { ?>',
		"twig_open" => '<?php
                    foreach($messages as $type => $armessage) {
                        foreach($armessage as $message) {
                ?>',
		                "twig_type" => '<?php echo $type; ?>',
		                "twig_message" => '<?php echo $message; ?>',
		                "twig_close" => '<?php } } ?>',
		                "twig_test_close" => '<?php } ?>'
		                		)),
		                		"><view" => array("", array("messages" => Junten::message()))
		));
		
		$mixedblocks = array();
		$diffs = array();
		foreach (Junten::$map->data as $el) {
			if (!is_array($el))
				continue;
			if (!isset($el["><block"]))
				continue;			
			
			$blocks = explode(".", $el["><block"]);
			if (count($blocks) > 1) {
				$diffs[] = $el["><id"];
				$the_keys = array_keys(Junten::$map->aliases, $blocks[0]);
				foreach ($the_keys as $the_key) {					
					if (!isset($mixedblocks[$the_key]))
						$mixedblocks[$the_key] = array();
					$template = call_user_func_array(array(Junten::$map, "build"), $el["><build"]);
					$el["><view"][1]["role"] = Junten::$theme;
					if (isset($el["><view"][1]["items_per_page"])) {
						$pg = Pagination::factory($el["><view"][1]);
						$pg->route_params(array(
								'user' => Junten::$theme,
								'controller' => strtolower($el["><view"][0]),
								'action' => 'liste'));
						$mixedblocks[$the_key][$blocks[1]] = $pg->render(Junten::$theme . "/$template");
					} else {
						if (isset($c["><build"][2]["><method"]) && $c["><build"][2]["><method"] == "message") {
							$mixedblocks[$the_key][$blocks[1]] = Liv::factory(
									Junten::$theme . "/$template", $el["><view"][1])->render();
						} else {
							$mixedblocks[$the_key][$blocks[1]] = Liv::factory(
									Junten::$theme . "/" . strtolower($el["><view"][0]) . "/$template", $el["><view"][1])->render();
						}
					}
				}
			}
		}
		
		foreach (Junten::$map->data as $c) {
			if (!isset($c["><id"])) {				
				$content = Junten::$map->data;
				break;
			}
			if (in_array($c["><id"], $diffs))
				continue;
		
			if (isset($mixedblocks[$c["><id"]])) {
				foreach ($mixedblocks[$c["><id"]] as $k => $v) {
					$c["><build"][2][$k] = '<?php echo $' . $k . '; ?>';
					$c["><view"][1][$k] = $v;
				}
			}
		
			$template = call_user_func_array(array(Junten::$map, "build"), $c["><build"]);
			$c["><view"][1]["role"] = Junten::$theme;
			
			if (isset($c["><view"][1]["items_per_page"])) {
				$pg = Pagination::factory($c["><view"][1]);
				$pg->route_params(array(
						'user' => Junten::$theme,
						'controller' => strtolower($c["><view"][0]),
						'action' => 'liste'));
				$content[$c["><block"]][] = $pg->render(Junten::$theme . "/$template");
			} else {
				if (isset($c["><build"][2]["><method"]) && $c["><build"][2]["><method"] == "message") {
					$content[$c["><block"]][] = Liv::factory(
							Junten::$theme . "/$template", $c["><view"][1])->render();
				} else {
					$content[$c["><block"]][] = Liv::factory(
							Junten::$theme . "/" . strtolower($c["><view"][0]) . "/$template", $c["><view"][1])->render();
				}
			}
			
			if($c["><block"] == "metabox") {
				$this->response->metabox ( Liv::factory (strtolower(Junten::$theme."/".$c["><view"][0]. "/") . $template, Junten::$map->globals() + $c["><view"][1]), strtolower($c["><view"][0]));
			}
		}
		
		$params = Junten::$map->globals() + $content;
		$mapped = Junten::$map->headers;
		if (isset(Junten::$map->{$this->request->action()})) {
			$mapped += Junten::$map->{$this->request->action()};
		}
		foreach ($mapped as $k => $k2) {
			if (isset($params[$k2])) {
				$mapped[$k] = $params[$k2];
			}
		}
		foreach ($params as $k => $v) {
			if (!isset($mapped[$k]))
				$mapped[$k] = $v;
		}
		
		return $this->twig->render("$this->template.html", $mapped);
	}
	
	public function hook_admin_notices() {
		Junten::message();
	}
}

?>

<?php

final class Lib_Controller {

	private $_props = array();
	private $_js = array(
			"js/jquery-1.10.2.min.js",
			"js/jquery-ui-1.10.3.custom.min.js"
	);
	private $_css = array(
			"themes/smoothness/jquery-ui-1.10.3.custom.min.css"
	);
	
	public function __construct($props = array()) {
		Junten::$controller = $this;
		//Junten::$online = TRUE;
		if(!defined('JQuery')) define("JQuery", $this->_js[0]);
        if(!defined('JQueryUI')) define("JQueryUI", $this->_js[1]);
        if(!defined('JQueryUIcss')) define("JQueryUIcss", $this->_css[0]);
		$props["allows"] = array(
				"index" => array("admin"),
				"liste" => array("admin"),
				"create" => array("admin"),
				"update" => array("admin"),
				"delete" => array("admin"),
				"export" => array("admin"),
				"detail" => array("admin"),
				"submit" => array("admin"),
				"duplicate" => array("admin"),
				"syncdb" => array("admin")
		);
		$props["options"] = false;
		$this->_props = $props;
	}
	
	public function get_perpage() {
		return 25;
	}
	
	public function checkboard($gets, $manies) {
		$checkboard = FALSE;
		foreach ($gets as $k_get => $v_get) {
			if (is_array($v_get) && array_intersect_key($v_get, $manies) && $gets[$k_get][key($v_get)] != "") {
				$checkboard[key($v_get)] = $k_get;
			}
		}
		return $checkboard;
	}
	
	public function checktable($slug, $primary_key, $checkboard = FALSE) {
		$checkables = array();
		if ($checkboard && is_array($checkboard)) {
			foreach ($checkboard as $k_check => $v_check) {
				$checkables[":checkable-" . $k_check] = array(
						":label" => array("text" => ""),
						":list" => array(
								"input" => array(
										"type" => "checkbox",
										"name" => "children[]",
										"value" => "{" . $primary_key . "}"
								)
						)
				);
			}
			//$this->_user("query", $this->request->query());
		} else {
			$checkables = array(":options" => array(
					":label" => array("text" => ""),
					":list" => array(
							":edit" => array(
									"a" => array(
											"href" => "admin/$slug/update/{" . $primary_key . "}",
											"text" => __("edit"),
											"class" => "button",
											"rel" => "gear"
									)
							),
							":delete" => array(
									"a" => array(
											"href" => "admin/$slug/delete/{" . $primary_key . "}",
											"text" => __("delete"),
											"class" => "button",
											"rel" => "trash",
											"onclick" => "return confirm('" . __("Are you sure to delete the record ?") . "');"
									)
							)
					)
			));
		}
		return $checkables;
	}
	
	public function getProp($name) {
		return $this->_props[$name];
	}
	
	public function hasProp($name) {
		return isset($this->_props[$name]);
	}
	
	private function included($file, $array) {
		$base = basename($file);
		foreach ($array as $a) {
			if (strlen($a) > strlen($base) && strripos($a, $base, -strlen($base)) > 0) {
				return TRUE;
				break;
			}
		}
		return FALSE;
	}
	
	public function js($code = "", $priority = NULL) {
		$this->_js = array_unique($this->_js);
		if ($code == "") {
			$scriptfiles = array();
			$scripts = array();
			$included = array();
			foreach ($this->_js as $script) {
				if (preg_match("/\.js$/i", $script)) {
					if (!$this->included($script, $included)) {
						$included[] = $script;
						$scriptfiles[] = HTML::script($script);
					}
				} else {
					$scripts[] = $script;
				}
			}
			$return = array();
			if (count($scriptfiles) > 0)
				$return[] = implode("\n", $scriptfiles);
			if (count($scripts) > 0)
				$return[] = '<script type="text/javascript">
      $(document).ready(function(){
	' . implode("\n", $scripts) . '
      });
            </script>';
			return implode("\n", $return);
		}
		if (!isset($priority))
			$this->_js[] = $code;
		else
			$this->_js[$priority] = $code;
		$this->_props["_js"] = $this->_js;
	}
	
	public function css($code = "", $priority = NULL) {
		if ($code == "clear")
			$this->_css = array();
		if ($code == "") {
			$this->_css = array_unique($this->_css);
			$stylefiles = array();
			$styles = array();
			$included = array();
			foreach ($this->_css as $k_style => $style) {
				if (preg_match("/\.css$/i", $style)) {
					if (!$this->included($style, $included)) {
						$included[] = $style;
						if ($attr = json_decode($k_style)) {
							unset($attr->hack);
							$stylefiles[] = HTML::style($style, (array) $attr);
						}
						else
							$stylefiles[] = HTML::style($style);
					}
				}
				else
					$styles[] = $style;
			}
			$return = array();
			if (count($stylefiles) > 0)
				$return[] = implode("\n", $stylefiles);
			if (count($styles) > 0)
				$return[] = '
    <style type="text/css">
      ' . implode("\n", $styles) . '
    </style>
';
			return implode("\n", $return);
		}
		if (!isset($priority))
			$this->_css[] = $code;
		elseif (is_array($priority)) {
			$priority["hack"] = uniqid("hackey");
			$this->_css[json_encode($priority)] = $code;
		}
		else
			$this->_css[$priority] = $code;
		$this->_props["_css"] = $this->_css;
	}
	
	public function wysiwyg() {
		$this->js('admin/jwysiwyg/jquery.wysiwyg.js');
		$this->css('admin/jwysiwyg/jquery.wysiwyg.css');
		$this->js('$("textarea").wysiwyg({
    controls: {
      strikeThrough : { visible : true },
      underline     : { visible : true },
	
      separator00 : { visible : true },
	
      justifyLeft   : { visible : true },
      justifyCenter : { visible : true },
      justifyRight  : { visible : true },
      justifyFull   : { visible : true },
	
      separator01 : { visible : true },
	
      indent  : { visible : true },
      outdent : { visible : true },
	
      separator02 : { visible : true },
	
      subscript   : { visible : true },
      superscript : { visible : true },
	
      separator03 : { visible : true },
	
      undo : { visible : true },
      redo : { visible : true },
	
      separator04 : { visible : true },
	
      insertOrderedList    : { visible : true },
      insertUnorderedList  : { visible : true },
      insertHorizontalRule : { visible : true },
	
      h4mozilla : { visible : true && $.browser.mozilla, className : "h4", command : "heading", arguments : ["h4"], tags : ["h4"], tooltip : "Header 4" },
      h5mozilla : { visible : true && $.browser.mozilla, className : "h5", command : "heading", arguments : ["h5"], tags : ["h5"], tooltip : "Header 5" },
      h6mozilla : { visible : true && $.browser.mozilla, className : "h6", command : "heading", arguments : ["h6"], tags : ["h6"], tooltip : "Header 6" },
	
      h4 : { visible : true && !( $.browser.mozilla ), className : "h4", command : "formatBlock", arguments : ["<H4>"], tags : ["h4"], tooltip : "Header 4" },
      h5 : { visible : true && !( $.browser.mozilla ), className : "h5", command : "formatBlock", arguments : ["<H5>"], tags : ["h5"], tooltip : "Header 5" },
      h6 : { visible : true && !( $.browser.mozilla ), className : "h6", command : "formatBlock", arguments : ["<H6>"], tags : ["h6"], tooltip : "Header 6" },
	
      separator07 : { visible : true },
	
      cut   : { visible : true },
      copy  : { visible : true },
      paste : { visible : true },
	
      html  : { visible : true }
    }
  });
');
	}
	
	public function initialize() {
		if ($this->hasProp("ORM") && DRIVER !== "heroku")
			$this->_props["options"] = call_user_func_array($this->_props["ORM"], array("Option"));
	}

}

?>

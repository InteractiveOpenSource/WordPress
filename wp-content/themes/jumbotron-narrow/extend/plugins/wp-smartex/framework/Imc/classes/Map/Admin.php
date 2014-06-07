<?php

class Map_Admin {

    public $data = array();
    public $aliases = array();
    public $index = array(
        "plugin" => "plugin",
        "title" => "title",
        "url" => "url",
        "content" => "list",
        "left" => "left",
        "translator" => "translator",
        "header" => "messages",
        "limit" => 5 //reserved,
    );
    public $article = array(
        "plugin" => "plugin",
        "title" => "title",
        "url" => "url",
        "content" => "list",
        "left" => "left",
        "translator" => "translator",
        "header" => "messages",
    );
    public $liste = array(
        "plugin" => "plugin",
        "title" => "title",
        "url" => "url",
        "content" => "list",
        "left" => "left",
        "translator" => "translator",
        "header" => "messages",
        "limit" => 5 //reserved,
    );
    public $add = array(
        "plugin" => "plugin",
        "title" => "title",
        "url" => "url",
        "translator" => "translator",
        "content" => "list",
        "limit" => 5 //reserved
    );
    public $edit = array(
        "plugin" => "plugin",
        "title" => "title",
        "url" => "url",
        "translator" => "translator",
        "content" => "list",
        "limit" => 5 //reserved
    );
    public $detail = array(
        "plugin" => "plugin",
        "title" => "title",
        "url" => "url",
        "translator" => "translator",
        "content" => "list",
        "header" => "messages",
        "limit" => 5 //reserved
    );
    public $search = array(
        "plugin" => "plugin",
        "title" => "title",
        "url" => "url",
        "translator" => "translator"
    );
    public $headers = array(
        "scripts" => "scripts",
        "styles" => "styles",
        "role" => "role",
        "base" => "base",
        "theme" => "theme"
    );
    
    private $params = array();
    
    public function globals() {
        $me = get_class($this);
        $theme = strtolower(str_replace("Map_", "", $me));
        if(Request::$current->action()=="editer" && Request::$current->controller()=="Login")
            Plugin::factory("Tagsinput")->render();
        Junten::$controller->js('$(".datepicker").datepicker({
            changeMonth : true,
            changeYear : true,
            yearRange : "-99y:+0y",
            dateFormat : "dd/mm/yy"
        });');
        return array_merge(array(
            "base" => Junten::base(),
            "theme" => Junten::site("themes/$theme"),
            "url" => array(
                "base" => Junten::site(),
                "user" => Junten::site($theme),
                "logout" => Junten::site("$theme/logout"),
                "logediter" => Junten::site("$theme/logediter")
            ),
            "translator" => new JuntenSeo(),
            "styles" => Junten::$controller->css(),
            "scripts" => Junten::$controller->js(),
            "role" => $theme,
            "title" => ucfirst($theme),
            "menus" => Lim::factory("Menu")->menu,
            "navs" => Lim::factory("Menu")->navigations
        ), $this->params);
    }

    public function build($model_name, $template, $s = array()) {    	
        if(Junten::$online)
            return $template;        
        
        $me = get_class($this);
        $theme = strtolower(str_replace("Map_", "", $me));
        $s["theme"] = '<?php echo Junten::site("themes/'.$theme.'"); ?>';
        $call = isset($s["><method"]) ? $s["><method"] : "";
        $creationPath = APPPATH . "views/$theme/" . strtolower($model_name);
        if (($call == "pagination" || $call == "message") && file_exists(APPPATH . "views/$theme/$template.php"))
            return $template;
        elseif ($call == "pagination" || $call == "message")
            $creationPath = APPPATH . "views/$theme";
        
        if (file_exists(APPPATH . "views/$theme/" . strtolower($model_name) . "/$template.php")  && !Request::$current->query("rebuild"))
            return $template;
        
        if (!is_dir(APPPATH . "views/$theme"))
        	mkdir(APPPATH . "views/$theme");
        
        if (!is_dir(APPPATH . "views/$theme/" . strtolower($model_name)))
            mkdir(APPPATH . "views/$theme/" . strtolower($model_name));

        $s["user"] = $theme;

        $loader = new Twig_Loader_Filesystem(array(
            DOCROOT . "themes/$theme/templates",
            DOCROOT . "themes/admin/templates"
        ));
        $twig = new Twig_Environment($loader, array("autoescape" => FALSE));
        $twig->addExtension(new Twig_Extension_Debug());
        
        $this->create_dir_if_needed($template, $creationPath);
        
        file_put_contents($creationPath . "/$template.php", $twig->render($template . ".html", $s));

        return $template;
    }

    public function fill($key, $value) {
        $id = uniqid();
        $value["><block"] = $key;
        $value["><id"] = $id;
        $this->data[] = $value;
        return $id;
    }
    
    public function set($key, $value) {
        $this->params[$key] = $value;
    }
    
    private function create_dir_if_needed($path, $root) {
        $dirs = explode("/", $path);
        if(count($dirs) > 1) {
            for($i = 0; $i<count($dirs)-1; $i++) {
                if(@mkdir($root."/".$dirs[$i]))
                    $root .= "/".$dirs[$i];
            }
        }
    }

}

?>

<?php

class Lib_Wordpress_Model extends Lib_Wordpress_Cpt {

	protected $_table_name;
	public $title;
	public $fields = array();
	private $meta_fields = array();
	public $add_uri, $uri;
	private $num_rows, $total;
	
	const ORM_METH__AND = "and_where";
	const ORM_METH__OR = "or_where";
	const SELECT_GATE = 5;
	
	public $messages = array(
			"action" => array(),
			"error" => array()
	);
	private $lastfill;

	public function __construct($id = NULL) {
		parent::__construct($id);
		
		$this->add_uri = $this->object_name() . "/add";
		
		Junten::$models[] = ucfirst($this->object_name());
	
		$errors = array();
		foreach ($this->get_fields() as $field_name => $field_params) {
			if (isset($field_params["errors"])) {
				foreach ($field_params["errors"] as $key => $value) {
					$errors[$field_name][$key] = $value;
				}
			}
		}
	
		$this->messages = array_merge(array(
				$this->object_name() => array(
						"insert_success" => I18n::get("insert successfully in :model", array(":model" => $this->title)),
						"update_success" => I18n::get("updated successfully in :model", array(":model" => $this->title)),
						"delete_success" => I18n::get("deleted successfully in :model", array(":model" => $this->title)),
				)), $errors);
		
		if ($this->loaded()) {
			foreach ($this->has_many() as $k => $v) {
				$this->$k->uri = $this->$k->object_name() . "/list/?" . $v["foreign_key"] . "=" . $this->pk();
			}
			foreach ($this->belongs_to() as $k => $v) {
				$this->$k->uri = $this->$k->object_name() . "/detail/" . $this->$k;
			}
		}
	}
	
	public function unlist($key) {
		$this->fields[$key] = null;
		unset($this->fields[$key]);
		return $this;
	}
	
	public function get_fields() {
		$fields = $this->fields;
		foreach ($fields as $k => $v) {
			if (!isset($v["title"]))
				$fields[$k]["title"] = $k;
			if (!isset($v["type"])) {
				if (isset($v["key"]) && $v["key"] == "primary") {
					$fields[$k]["type"] = "hidden";
				} else {
					$fields[$k]["type"] = "text";
				}
			}
			if (!isset($v["html"]))
				$fields[$k]["html"] = array();
		}
		return $fields;
	}
	
	public function add_meta_field($name, $value) {
		$this->meta_fields[$name] = $value;
		$this->fields[$name] = $value;
	}
	
	private function remove_meta_fields($array) {
		return array_diff_key($array, $this->meta_fields);
	}
	
	public function __get($column) {
		$fields = $this->get_fields();
		$fields = $this->remove_meta_fields($fields);
		if(isset($fields[$column]) && isset($fields[$column]["select"])) {
			$value = $this->getValue($column);
			$value = isset($fields[$column]["select"][$value]) ? $fields[$column]["select"][$value] : $value;
			return $value;
		}
		if ($column == "detail_uri") {
			return $this->object_name() . "/detail/" . $this->pk();
		}
		if ($column == "edit_uri") {
			return $this->object_name() . "/edit/" . $this->pk();
		}
		if ($column == "delete_uri") {
			return $this->object_name() . "/delete/" . $this->pk();
		}
		if (isset($this->fields[$column]) && isset($this->fields[$column]["type"]) && ($this->fields[$column]["type"] == "date" || $this->fields[$column]["type"] == "time")) {
			$date = $this->getValue($column);
			return Junten::encode_date($date);
		}
		return $this->getValue($column);
	}
	
	public function __isset($column) {
		if ($column == "detail_uri" || $column == "edit_uri" || $column == "delete_uri")
			return TRUE;
		return parent::__isset($column);
	}
	
	/**
	 *
	 * @param array $data
	 * @param int $offset
	 * @param array $flags
	 * @param string $template
	 * @param string $block
	 * @return Lim
	 */
	public function perform_liste($data = NULL, $offset = 0, $flags = array(), $template = "list-table", $block = "list") {
		if (isset($data["_export"]) && $data["_export"] != "") {
			$this->export($data);
		}
		$model_fields = $this->get_fields();
		$model_fields = $this->remove_meta_fields($model_fields);
		$fields = array();
		/* filtre sur ordre */
		$ar = array();
		foreach ($model_fields as $field_name => $field_params) {
			$model_fields[$field_name]["toggle"] = "ASC";
			if (isset($data["_field"]) && $data["_field"] == $field_name)
				$model_fields[$field_name]["toggle"] = ($data["_order"] == "ASC") ? "DESC" : "ASC";
			if ($field_params["type"] == "password")
				continue;
			if (!isset($model_fields[$field_name]["html"]))
				$model_fields[$field_name]["html"] = array();
			$ar[$field_name] = array(
					"twig_item" => '<?php echo $row->' . $field_name . '; ?>',
					"twig_title" => '<?php echo HTML::anchor(Request::current()->uri() . "?_field=' . $field_name . '&_order=" . $fields["' . $field_name . '"]["toggle"], "' . I18n::get($model_fields[$field_name]["title"]) . '", $fields["' . $field_name . '"]["html"]); ?>'
			);
		}
		$flags = array_replace(array("method" => "=", "like_method" => "LIKE", "begins" => "%", "ends" => "%", "and_or" => self::ORM_METH__AND), $flags);
		$and_or = $flags["and_or"];
		if (is_array($data)) {
			$inpair = array();
			foreach ($data as $k_data => $v_data) {
				if (is_array($v_data)) {
					foreach ($v_data as $k_v_data => $v_v_data) {
						if ($v_v_data != "")
							$fields[$k_v_data][$k_data] = $v_v_data;
					}
				}
				elseif ($v_data != "")
				$fields[$this->object_name()][$k_data] = $v_data;
			}
		}
		$belongs_to = $bt = $this->belongs_to();
		$has_many = $hm = $this->has_many();
		$bar = array();
		$har = array();
		if (count($belongs_to) > 0) {
			$add_constraints = array();
			foreach ($bt as $i_belongs_to => $v_belongs_to) {
				if (isset($data[$v_belongs_to["foreign_key"]])) {
					$add_constraints[] = $v_belongs_to["foreign_key"] . '=' . $data[$v_belongs_to["foreign_key"]];
					$bar[strtolower($v_belongs_to["model"]) . '/detail/' . $data[$v_belongs_to["foreign_key"]]] = I18n::get($this->$i_belongs_to->title);
				}
				$this->join(array($this->$i_belongs_to->table_name(), $i_belongs_to), "LEFT")
				->on(DB::expr($this->object_name() . "." . $bt[$i_belongs_to]["foreign_key"]), "=", DB::expr($i_belongs_to . "." . $this->primary_key()));
			}
			$this->add_uri .= "?" . implode("&", $add_constraints);
		} elseif (count($has_many) > 0) {
			foreach ($hm as $i_has_many => $v_has_many) {
				$har[] = array(
						"twig_has_many_link" => '<?php echo HTML::anchor($role . "/' . strtolower($v_has_many["model"]) . '/liste?' . strtolower($v_has_many["foreign_key"]) . '=" . $row->' . $this->primary_key() . ', "' . I18n::get($this->$i_has_many->title) . '"); ?>'
				);
				if (isset($fields[$i_has_many]) && !isset($v_has_many["through"])) {
					$this->join(array($this->$i_has_many->table_name(), $i_has_many), "LEFT")
					->on(DB::expr($this->object_name() . "." . $this->primary_key()), "=", DB::expr($i_has_many . "." . $v_has_many["foreign_key"]));
				} elseif (isset($fields[$i_has_many]) && isset($v_has_many["through"])) {
					$this->join(array($v_has_many["through"], $i_has_many))
					->on(DB::expr($this->object_name() . "." . $this->primary_key()), "=", DB::expr($i_has_many . "." . $v_has_many["foreign_key"]));
				}
			}
		}
		$as_texts = array("text", "html");
		$as_dates = array("time", "date");
		foreach ($fields as $k_field => $v_field) {
			foreach ($v_field as $k_v_field => $v_v_field) {
				if ($k_v_field == "_export")
					continue;
				if ($v_v_field !== "") {
					if ($v_v_field === "NULL")
						$this->$and_or(DB::expr($k_field . "." . $k_v_field), $flags["method"], NULL);
					elseif ($v_v_field == "NONE" && array_key_exists($k_v_field, $hm)) {
						$this->$and_or(DB::expr($this->object_name() . "." . $this->primary_key()), "NOT IN", DB::select($hm[$k_v_field]["foreign_key"])->from($this->$k_v_field->table_name()));
					} elseif (( (array_key_exists($k_v_field, $model_fields) && in_array($model_fields[$k_v_field]["type"], $as_texts)) || (isset($this->$k_field) && array_key_exists($k_v_field, $this->$k_field->fields) && in_array($this->$k_field->fields[$k_v_field]["type"], $as_texts)))) {
						$this->$and_or(DB::expr($k_field . "." . $k_v_field), $flags["like_method"], $flags["begins"] . $v_v_field . $flags["ends"]);
					} elseif ((array_key_exists($k_v_field, $model_fields) && in_array($model_fields[$k_v_field]["type"], $as_dates)) || (isset($this->$k_field) && array_key_exists($k_v_field, $this->$k_field->fields) && in_array($this->$k_field->fields[$k_v_field]["type"], $as_dates))) {
						$this->$and_or(DB::expr($k_field . "." . $k_v_field), $flags["method"], $v_v_field);
					} elseif (preg_match("/^(_max|_min|_range)/i", $k_v_field) && ((array_key_exists(preg_replace("/^(_max|_min|_range)/i", "", $k_v_field), $model_fields) && !in_array($v_field, $inpair)) || (isset($this->$k_field) && array_key_exists(preg_replace("/^(_max|_min|_range)/i", "", $k_v_field), $this->$k_field->fields) && !in_array($v_field, $inpair)))) {
						$ch = preg_replace("/^(_max|_min|_range)/i", "", $k_v_field);
						if ((isset($fields[$k_field]["_min" . $ch]) && isset($fields[$k_field]["_max" . $ch]) && $fields[$k_field]["_min" . $ch] != "" && $fields[$k_field]["_max" . $ch] != "") || (isset($fields[$k_field]["_range" . $ch]) && $fields[$k_field]["_range" . $ch] != "")) {
							if (isset($fields[$k_field]["_range" . $ch])) {
								$range = explode(":", $fields[$k_field]["_range" . $ch]);
								$fields[$k_field]["_min" . $ch] = $range[0];
								$fields[$k_field]["_max" . $ch] = $range[1];
							}
							$this->$and_or(DB::expr($k_field . "." . $ch), "BETWEEN", array($fields[$k_field]["_min" . $ch], $fields[$k_field]["_max" . $ch]));
							array_push($inpair, $v_field);
						} elseif (isset($fields[$k_field]["_min" . $ch]) && $fields[$k_field]["_min" . $ch] != "")
						$this->$and_or(DB::expr($k_field . "." . $ch), ">=", $fields[$k_field]["_min" . $ch]);
						elseif (isset($fields[$k_field]["_max" . $ch]) && $fields[$k_field]["_max" . $ch] != "") {
							$this->$and_or(DB::expr($k_field . "." . $ch), "<=", $fields[$k_field]["_max" . $ch]);
						}
					} elseif (array_key_exists($k_v_field, $model_fields) || array_key_exists($k_field, $this->has_many())) {
						$this->$and_or(DB::expr($k_field . "." . $k_v_field), $flags["method"], $v_v_field);
					}
				}
			}
		}
	
		if (is_array($data) && isset($data["_field"]) && isset($data["_order"]) && array_key_exists($data["_field"], $this->fields) && ($data["_order"] == "ASC" || $data["_order"] == "DESC")) {
			$this->order_by(DB::expr($this->object_name() . "." . $data["_field"]), $data["_order"]);
			$data["_field"] = NULL;
			$data["_order"] = NULL;
			unset($data["_field"], $data["_order"]);
		}
	
		$mapped = Junten::$map->liste;
		if (!isset($data["_offset"]))
			$data["_offset"] = ((int) $offset > 0) ? (($offset - 1) * $mapped["limit"]) : 0;
		$data["_limit"] = isset($mapped["limit"]) ? $mapped["limit"] : -1;
		if (isset($data["_limit"]) && isset($data["_offset"])) {
			$this->total = $this->reset(FALSE)->count_all();
			$this->offset($data["_offset"])->limit($data["_limit"]);
			$this->num_rows = $data["_limit"];
		}
	
		$liste = array(
				"total" => $this->total,
				"fields" => $model_fields,
				"model" => $this,
				"rows" => $this->reset(FALSE)->find_all(),
				"belongs_to_links" => $bar
		);
		$this->lastfill = Junten::$map->fill($block, array(
				"><build" => array($this->object_name(), $template, array(
						"twig_title" => $this->object_name() . ' <?php echo Junten::plural("%s record", $total); ?>',
						"fields" => $ar,
						"has_many" => $har,
						"twig_belongs_to_links" => '<?php foreach($belongs_to_links as $belongs_to_link_key => $belongs_to_link_value) {
                        echo HTML::anchor($role . "/" . $belongs_to_link_key, $belongs_to_link_value);
                    } ?>',
						"twig_total" => '<?php echo $total; ?>',
						"twig_loop_open" => '<?php foreach($rows as $row) { ?>',
						"twig_loop_close" => '<?php } ?>',
						"twig_detail_link" => '<?php echo HTML::anchor($role . "/" . $row->detail_uri, "' . I18n::get("detail") . '"); ?>',
						"twig_edit_link" => '<?php echo HTML::anchor($role . "/" . $row->edit_uri, "' . I18n::get("edit") . '"); ?>',
						"twig_delete_link" => '<?php echo HTML::anchor($role . "/" . $row->delete_uri, "' . I18n::get("delete") . '"); ?>',
						"twig_add_link" => '<?php echo HTML::anchor($role . "/" . $model->add_uri, "' . I18n::get("add") . '"); ?>'
				)),
				"><view" => array($this->object_name(), $liste)
		));
	
		return $this;
	}
	
	/**
	 *
	 * @param string $template
	 * @param string $block
	 * @return Lim
	 */
	public function perform_pagination($template = "basic", $block = "list") {
		$this->lastfill = Junten::$map->fill($block, array(
				"><build" => array($this->object_name(), $template, array(
						"><method" => "pagination",
						"twig_first_page" => '<?php echo $first_page ? HTML::chars($page->url($first_page)) : "#"; ?>',
						"twig_previous_page" => '<?php echo $previous_page ? HTML::chars($page->url($previous_page)) : "#"; ?>',
						"twig_current_page" => '<?php echo $current_page ?>',
						"twig_total_page" => '<?php echo $total_pages; ?>',
						"twig_next_page" => '<?php echo $next_page ? HTML::chars($page->url($next_page)) : "#"; ?>',
						"twig_last_page" => '<?php echo $last_page ? HTML::chars($page->url($last_page)) : "#"; ?>',
						"twig_open" => '<?php for($i=Lim::pagination($current_page, $total_pages)->start; $i<=Lim::pagination($current_page, $total_pages)->end; $i++) { ?>',
						"twig_close" => '<?php } ?>',
						"twig_loop_uri" => '<?php echo HTML::chars($page->url($i)) ?>',
						"twig_loop_test" => '<?php echo ($i==$current_page) ? " class=\"active\"" : ""; ?>',
						"twig_loop_title" => '<?php echo $i; ?>'
				)),
				"><view" => array($this->object_name(), array(
						'current_page' => array('source' => 'route', 'key' => 'page'),
						'items_per_page' => $this->num_rows,
						'total_items' => $this->total,
						'auto_hide' => TRUE,
						'first_page_in_url' => FALSE,
						'view' => 'pagination/' . $template
				))
		));
	
		return $this;
	}
	
	private function form() {
		$fields = $this->get_fields();
	
		$ar = array();
	
		foreach ($fields as $field_name => $field_params) {
			if ($field_params["type"] == "hidden")
				continue;
			if (isset($field_params["key"]) && $field_params["key"] == "primary")
				continue;
			if ($field_params["type"] == "time")
				continue;
			if (!isset($field_params["html"])) {
				$fields[$field_name]["html"] = array();
			}
			if (isset($field_params["select"])) {
				if (count($field_params["select"]) < self::SELECT_GATE) {
					$opts = array();
					foreach ($field_params["select"] as $kopt => $opt) {
						$opts[] = '<?php echo Junten::radio("' . $field_name . '", "' . $kopt . '", $prefills->' . $field_name . ' == "' . $opt . '"); ?>';
						$opts[] = '<?php echo Junten::label("' . $field_name . '_' . $kopt . '", "' . I18n::get($opt) . '"); ?>';
					}
					$input = implode("\n", $opts);
				} else {
					$input = '<?php echo Junten::select("' . $field_name . '", array("" => "' . I18n::get("please select") . '") + $fields["' . $field_name . '"]["select"], $prefills->' . $field_name . '); ?>';
				}
			} elseif ($field_params["type"] == "html") {
				$input = '<?php echo Junten::textarea("' . $field_name . '", $prefills->' . $field_name . ', $fields["' . $field_name . '"]["html"]); ?>';
			} elseif ($field_params["type"] == "password") {
				$input = '<?php echo Junten::password("' . $field_name . '"); ?>';
			} elseif ($field_params["type"] == "image" || $field_params["type"] == "file") {
				$input = '<?php echo Junten::file("' . $field_name . '") ?>';
			} else {
				$input = '<?php echo Junten::input("' . $field_name . '", $prefills->' . $field_name . ', $fields["' . $field_name . '"]["html"]); ?>';
			}
			if ($field_params["type"] == "password") {
				$ar[$field_name . "_old"] = array(
						"twig_label" => '<?php echo Junten::label("' . $field_name . '_old", "' . I18n::get("old " . $field_params["title"]) . '"); ?>',
						"twig_input" => '<?php echo Junten::password("' . $field_name . '_old"); ?>',
						"twig_open" => '<?php if(isset($prefills->' . $field_name . ')) { ?>',
						"twig_close" => '<?php } ?>'
				);
			}
			$ar[$field_name] = array(
					"twig_label" => '<?php echo Junten::label("' . $field_name . '", "' . I18n::get($field_params["title"]) . '"); ?>',
					"twig_input" => $input
			);
			if ($field_params["type"] == "password") {
				$ar[$field_name . "_confirm"] = array(
						"twig_label" => '<?php echo Junten::label("' . $field_name . '_confirm", "' . I18n::get("confirm " . $field_params["title"]) . '"); ?>',
						"twig_input" => '<?php echo Junten::password("' . $field_name . '_confirm"); ?>'
				);
			}
		}
	
		return $ar;
	}
	
	/**
	 *
	 * @param array $data
	 * @param string $template
	 * @param string $block
	 * @return Lim
	 */
	public function perform_add($data, $template = "form-table", $block = "list") {
		$fields = $this->get_fields();
		$prefills = new stdClass();
		$hiddens = array();
		$controller_hiddens = array_diff_key($data, $fields);
		foreach ($controller_hiddens as $key => $value) {
			$hiddens[$key] = Junten::hidden($key, $value);
		}
		foreach ($fields as $field_name => $field_params) {
			if (isset($field_params["select"]) && !isset($data[$field_name])) {
				$keys = array_values($field_params["select"]);
				$prefills->$field_name = count($keys) > 0 ? $keys[0] : "";
			} elseif ($field_params["type"] != "password")
			$prefills->$field_name = isset($data[$field_name]) ? $data[$field_name] : "";
			if ($field_params["type"] == "hidden") {
				$hiddens[$field_name] = Junten::hidden($field_name, $prefills->$field_name);
				continue;
			}
			if (isset($field_params["key"]) && $field_params["key"] == "primary")
				continue;
			if ($field_params["type"] == "time")
				continue;
			if (!isset($field_params["html"])) {
				$fields[$field_name]["html"] = array();
			}
			if ($field_params["type"] == "image" || $field_params["type"] == "file") {
				if($prefills->$field_name != "")
					$hiddens["_prev" . $field_name] = Junten::hidden("_prev" . $field_name, $prefills->$field_name);
	
				if (!isset($field_params["size"]))
					$field_params["size"] = array("320x320", "640x480");
				foreach ($field_params["size"] as $size) {
					$hiddens[$field_name . "_size_" . $size] = Junten::hidden($field_name . "_size[]", $size);
				}
			}
		}
	
		$this->lastfill = Junten::$map->fill($block, array(
				"><build" => array($this->object_name(), $template, array(
						"fields" => $this->form(),
						"twig_hiddens" => '<?php echo $hiddens; ?>',
						"twig_submit" => '<?php echo Junten::submit("submit", "' . I18n::get("valider") . '"); ?>',
						"twig_open" => '<?php echo Junten::open($role . "/" . $form["action"], $form["html"]); ?>',
						"twig_close" => '<?php echo Junten::close(); ?>',
				)),
				"><view" => array($this->object_name(), array(
						"role" => Junten::$theme, //will be removed
						"form" => array(
								"action" => strtolower($this->object_name()) . "/insert",
								"html" => array(
										"name" => "frm_" . $this->object_name(),
										"method" => "POST",
										"enctype" => "multipart/form-data"
								)
						),
						"fields" => $fields,
						"hiddens" => implode("\n", $hiddens),
						"prefills" => $prefills
				))
		));
	
		return $this;
	}
	
	/**
	 *
	 * @param object $prefills
	 * @param string $template
	 * @param string $block
	 * @return Lim
	 */
	public function perform_edit($prefills, $template = "form-table", $block = "list") {
		$fields = $this->get_fields();
		$hiddens = array();
		$data = (array) $prefills;
		$controller_hiddens = array_diff_key($data, $fields);
		foreach ($controller_hiddens as $key => $value) {
			$hiddens[$key] = Junten::hidden($key, $value);
		}
		foreach ($fields as $field_name => $field_params) {
			if ($field_params["type"] == "select") {
				$prefills->$field_name = array_keys($field_params["select"], $prefills->$field_name);
			}
	
			if ($field_params["type"] == "hidden") {
				$hiddens[$field_name] = Junten::hidden($field_name, $prefills->$field_name);
				continue;
			} elseif (isset($field_params["key"]) && $field_params["key"] == "primary") {
				$hiddens[$field_name] = Junten::hidden($field_name, $prefills->$field_name);
				continue;
			} else {
				if ($field_params["type"] == "image" || $field_params["type"] == "file") {
					$hiddens["_prev" . $field_name] = Junten::hidden("_prev" . $field_name, $prefills->$field_name);
					if ($field_params["type"] == "image") {
						if (!isset($field_params["size"]))
							$field_params["size"] = array("320x320", "640x480");
						foreach ($field_params["size"] as $size) {
							$hiddens[$field_name . "_size_" . $size] = Junten::hidden($field_name . "_size[]", $size);
						}
					}
				}
			}
		}
		$this->lastfill = Junten::$map->fill($block, array(
				"><build" => array($this->object_name(), $template, array(
						"fields" => $this->form(),
						"twig_hiddens" => '<?php echo $hiddens; ?>',
						"twig_submit" => '<?php echo Junten::submit("submit", "' . I18n::get("valider") . '"); ?>',
						"twig_open" => '<?php echo Junten::open($role . "/" . $form["action"], $form["html"]); ?>',
						"twig_close" => '<?php echo Junten::close(); ?>',
				)),
				"><view" => array($this->object_name(), array(
						"role" => Junten::$theme,
						"form" => array(
								"action" => strtolower($this->object_name()) . "/update",
								"html" => array(
										"name" => "frm_" . $this->object_name(),
										"method" => "POST",
										"enctype" => "multipart/form-data"
								)
						),
						"fields" => $fields,
						"hiddens" => implode("\n", $hiddens),
						"prefills" => $prefills
				))
		));
	
		return $this;
	}
	
	/**
	 *
	 * @param string $template
	 * @param string $block
	 * @return Lim
	 */
	public function perform_detail($template = "detail-table", $block = "list") {
		$fields = $this->get_fields();
		$ar = array();
		foreach ($fields as $field_name => $field_params) {
			if ($field_params["type"] == "password" || (isset($field_params["key"]) && $field_params["key"] == "primary"))
				continue;
	
			if ($field_params["type"] == "image") {
				$ar[$field_name] = array(
						"title" => I18n::get($field_params["title"]),
						"twig" => '<?php echo HTML::image("upload/" . $model->' . $field_name . '); ?>'
				);
			} else {
				$ar[$field_name] = array(
						"title" => I18n::get($field_params["title"]),
						"twig" => '<?php echo $model->' . $field_name . '; ?>'
				);
			}
		}
		foreach ($this->has_many() as $key => $value) {
			$ar[$key] = array(
					"title" => "",
					"twig" => '<?php echo HTML::anchor(Junten::$theme . "/' . strtolower($value["model"]) . '/liste?' . $value["foreign_key"] . '=" . $model->' . $this->primary_key() . ', "' . I18n::get($this->$key->title) . '"); ?>'
			);
		}
		foreach ($this->belongs_to() as $key => $value) {
			$ar[$key] = array(
					"title" => "",
					"twig" => '<?php echo HTML::anchor(Junten::$theme . "/" . $model->' . $key . '->detail_uri, "' . I18n::get($this->$key->title) . '"); ?>'
			);
		}
		$this->lastfill = Junten::$map->fill($block, array(
				"><build" => array($this->object_name(), $template, array("fields" => $ar)),
				"><view" => array($this->object_name(), array("model" => $this))
		));
	
		return $this;
	}
	
	private function export($data) {
		Junten::load(MODPATH . "junten/classes/PHPExcel.php");
		$headings = array();
		$lines = array();
		$fields = $this->get_fields();
		$fields = $this->remove_meta_fields($fields);
		//lines are default list
		foreach ($fields as $field_name => $field_params) {
			$headings[] = I18n::get($field_params["title"]);
			$lines[$field_name] = isset($field_params["list"]) ? $field_params["list"] : $field_name;
		}
		$array = array($headings);
		$format = $data["_export"];
		$data["_export"] = NULL;
		unset($data["_export"]);
		if ($this->perform_liste($data)->reset(FALSE)->count_all() > 500) {
			Junten::message("database", "overload", I18n::get("Please select less records to export."));
			return;
		}
		$rows = $this->perform_liste($data)->find_all();
		foreach ($rows as $row) {
			$line = array();
			foreach ($lines as $v) {
				$repls = explode(".", $v);
				$r = $row;
				foreach ($repls as $replac) {
					$r = $r->$replac;
				}
				$line[] = $r;
			}
			$array[] = $line;
		}
	
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()
		->setTitle($this->title)
		->setSubject($this->title)
		->setDescription("Export for " . $this->title)
		->setKeywords("office 2007 openxml php")
		->setCategory($this->object_name());
		$irow = 1;
		foreach ($array as $row) {
			$icol = 0;
			foreach ($row as $col) {
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($icol, $irow, $col);
				$icol++;
			}
			$irow++;
		}
		switch ($format) {
			default:
			case "Excel2007":
				$objPHPExcel->getActiveSheet()->getStyle('A1:AAA1')->getFill()
				->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()->setRGB('F9C000');
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="' . $this->object_name() . date("Y-m-d-H-i-s") . '.xlsx"');
				header('Cache-Control: max-age=0');
				break;
			case "Excel5":
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $this->object_name() . date("Y-m-d-H-i-s") . '.xls"');
				header('Cache-Control: max-age=0');
				break;
		}
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $format);
		$objWriter->save('php://output');
		exit;
	}
	
	/**
	 *
	 * @param array $data
	 * @param array $files
	 * @return Lim
	 */
	public function perform_insert($data, $files) {
		$model_fields = $this->get_fields();
		$model_fields = $this->remove_meta_fields($model_fields);
		$is_user_registration = FALSE;
		$file_validation = Validation::factory($files);
		foreach ($model_fields as $field_name => $field_params) {
			if ($field_params["type"] == "file" && isset($files[$field_name]))
				$file_validation->rule($field_name, "Upload::type", array(":value", array("png", "jpeg", "jpg", "gif", "flv", "doc", "docx", "pdf", "xls", "xlsx")));
	
			if ($field_params["type"] == "image" && isset($files[$field_name]))
				$file_validation->rule($field_name, "Upload::type", array(':value', array('jpg', 'jpeg', 'png', 'gif')));
	
			if(($field_params["type"] == "image" || $field_params["type"] == "file")
			&& isset($files[$field_name]) && isset($data["_prev".$field_name]))
				$data[$field_name] = $data["_prev".$field_name];
		}
	
		if ($file_validation->check()) {
			foreach ($model_fields as $field_name => $field_params) {
				if (isset($files[$field_name]) && ($field_params["type"] == "file" || $field_params["type"] == "image")) {
					$file = Upload::save($files[$field_name], uniqid("jt") . $files[$field_name]["name"], "upload");
					if ($file && $field_params["type"] == "image") {
						if (isset($data[$field_name . "_size"])) {
							$ar = $data[$field_name . "_size"];
							if (is_array($ar)) {
								foreach ($ar as $size) {
									list($w, $h) = explode("x", $size);
									Image::factory($file)->resize($w, $h);
								}
							} else {
								list($w, $h) = explode("x", $ar);
								Image::factory($file)->resize($w, $h);
							}
						}
						else
							Image::factory($file)->resize(640, 480);
					}
					if($file)
						$data[$field_name] = basename($file);
				}
			}
		}
	
		$validation = Validation::factory($data);
		$validation->bind(":model", $this);
		foreach ($model_fields as $field_name => $field_params) {
			if ($field_params["type"] == "password") {
				$validation->rule($field_name, "not_empty")
				->rule($field_name . "_confirm", "matches", array(":validation", ":field", $field_name));
				$is_user_registration = $data[$field_name];
				$data[$field_name] = Auth::instance()->hash($data[$field_name]);
			} elseif (isset($field_params["errors"])) {
				foreach ($field_params["errors"] as $callback => $message) {
					if (strpos($callback, "::") > 0)
						$validation->rule($field_name, $callback, array(":model", ":validation", ":value"));
					else
						$validation->rule($field_name, $callback);
				}
			}
			if ($field_params["type"] == "date") {
				$data[$field_name] = Junten::decode_date($data[$field_name]);
			}
		}
	
		$errors = $validation->errors();
	
		if ($validation->check()) {
			if ($is_user_registration) {
				/* $user = ORM::factory("User")->create_user(
				 array("username" => "admin",
				 		"password" => $is_user_registration,
				 		"password_confirm" => $is_user_registration,
				 		"email" => $email), array('username', 'password', 'email')
				);
				$user->add("roles", ORM::factory("Role", array("name" => "login")));
				$user->add("roles", ORM::factory("Role", array("name" => strtolower($this->model_name)))); */
			}
			Junten::message("database", $this->object_name() . ".insert_success");
			return $this->edit($data);
		} else {
			Junten::append($file_validation->errors("database"));
			Junten::append($validation->errors("database"));
			foreach ($data as $k => $v) {
				if (isset($model_fields[$k]["select"]) && isset($model_fields[$k]["select"][$v])) {
					$data[$k] = $model_fields[$k]["select"][$v];
				}
				else
					$data[$k] = $v;
			}
			$this->perform_add($data);
			return;
		}
		return $this;
	}
	
	/**
	 *
	 * @param array $data
	 * @param array $files
	 * @return Lim
	 */
	public function perform_update($data, $files) {
		$file_validation = Validation::factory($files);
		$model_fields = $this->get_fields();
		$model_fields = $this->remove_meta_fields($model_fields);
		foreach ($model_fields as $field_name => $field_params) {
			if ($field_params["type"] == "file")
				$file_validation->rule($field_name, "Upload::type", array(":value", array("png", "jpeg", "jpg", "gif", "flv", "doc", "docx", "pdf", "xls", "xlsx")));
			if ($field_params["type"] == "image")
				$file_validation->rule($field_name, "Upload::type", array(':value', array('jpg', 'jpeg', 'png', 'gif')));
		}
		if ($file_validation->check()) {
			foreach ($model_fields as $field_name => $field_params) {
				if ($field_params["type"] == "file" || $field_params["type"] == "image") {
					if (isset($data["_prev" . $field_name]))
						@unlink(Junten::site("upload/" . $data["_prev" . $field_name]));
	
					$file = Upload::save($files[$field_name], uniqid("jt") . $files[$field_name]["name"], "upload");
					if ($field_params["type"] == "image") {
						if (isset($data[$field_name . "_size"])) {
							$ar = $data[$field_name . "_size"];
							if (is_array($ar)) {
								foreach ($ar as $size) {
									list($w, $h) = explode("x", $size);
									Image::factory($file)->resize($w, $h);
								}
							} else {
								list($w, $h) = explode("x", $ar);
								Image::factory($file)->resize($w, $h);
							}
						}
						else
							Image::factory($file)->resize(640, 480);
					}
					$data[$field_name] = basename($file);
				}
			}
		}
	
		$validation = Validation::factory($data);
		$validation->bind(":model", $this);
		foreach ($model_fields as $field_name => $field_params) {
			if ($field_params["type"] == "password") {
				$validation->rule($field_name . "_old", "not_empty")
				->rule($field_name . "_old", "Lim::matches", array(":validation", ":field", ":value"));
				$validation->rule($field_name, "not_empty")
				->rule($field_name . "_confirm", "matches", array(":validation", ":field", $field_name));
				$data[$field_name] = Auth::instance()->hash($data[$field_name]);
			} elseif (isset($field_params["errors"])) {
				foreach ($field_params["errors"] as $callback => $message) {
					if (strpos($callback, "::") > 0) {
						$validation->rule($field_name, $callback, array(":model", ":validation", ":value"));
					} else {
						$validation->rule($field_name, $callback);
					}
				}
			}
			if ($field_params["type"] == "date") {
				$data[$field_name] = Junten::decode_date($data[$field_name]);
			}
		}
		if ($validation->check()) {
			Junten::message("database", $this->object_name() . ".update_success");			
			return $this->edit($data);
		} else {
			Junten::append($file_validation->errors("database"));
			Junten::append($validation->errors("database"));
			$this->perform_edit($this);
			return;
		}
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function perform_delete() {
		if ($this->loaded()) {
			$this->delete();
			Junten::message("database", $this->object_name() . ".delete_success");
		}
		else
			Junten::message(I18n::get("Could not delete the record !"), "error");
		return Request::current()->uri();
	}
	
	/**
	 *
	 * @param array $data
	 * @param string $template
	 * @param string $block
	 * @return Lim
	 */
	public function perform_search($data, $template = "search", $block = "list") {
		$fields = $this->get_fields();
	
		$prefills = new stdClass();
		$prefills->_export = isset($data["_export"]) ? $data["_export"] : FALSE;
		$prefills->_order = isset($data["_order"]) ? $data["_order"] : "";
		$prefills->_field = isset($data["_field"]) ? $data["_field"] : "";
		foreach ($fields as $field_name => $field_params) {
			if ($field_params["type"] != "password")
				$prefills->$field_name = isset($data[$field_name]) ? $data[$field_name] : "";
	
			if ($field_params["type"] == "numeric" || $field_params["type"] == "time") {
				$prefills->{"_min" . $field_name} = isset($data["_min" . $field_name]) ? $data["_min" . $field_name] : "";
				$prefills->{"_max" . $field_name} = isset($data["_max" . $field_name]) ? $data["_max" . $field_name] : "";
			}
		}
		$ar = array();
		$order_options = array('"" => "' . I18n::get("order by...") . '"');
		foreach ($fields as $field_name => $field_params) {
			if ($field_params["type"] == "password")
				continue;
	
			$order_options[] = '"' . $field_name . '" => "' . $field_params["title"] . '"';
	
			if (isset($field_params["select"])) {
				$input = '<?php echo Junten::select("' . $field_name . '", array("" => I18n::get("please select")) + $fields["' . $field_name . '"]["select"], $prefills->' . $field_name . ', $fields["' . $field_name . '"]["html"]); ?>';
			} elseif ($field_params["type"] == "image" || $field_params["type"] == "file") {
				continue;
			} elseif ($field_params["type"] == "numeric" || $field_params["type"] == "time") {
				$input = '<?php echo Junten::label("_min' . $field_name . '", I18n::get("entre"));
                echo Junten::input("_min' . $field_name . '", $prefills->_min' . $field_name . ', $fields["' . $field_name . '"]["html"]);
                echo Junten::label("_max' . $field_name . '", I18n::get("et"));
                echo Junten::input("_max' . $field_name . '", $prefills->_max' . $field_name . ', $fields["' . $field_name . '"]["html"]); ?>';
			} else {
				$input = '<?php echo Junten::input("' . $field_name . '", $prefills->' . $field_name . ', $fields["' . $field_name . '"]["html"]); ?>';
			}
	
			$ar[$field_name] = array(
					"twig_label" => '<?php echo Junten::label("' . $field_name . '", "' . I18n::get($field_params["title"]) . '"); ?>',
					"twig_input" => $input
			);
		}
	
		$this->lastfill = Junten::$map->fill($block, array(
				"><build" => array($this->object_name(), $template, array(
						"fields" => $ar,
						"twig_order_select" => '<?php echo Junten::select("_field", array(' . implode(",", $order_options) . '), $prefills->_field); ?>',
						"twig_order_radio" => '<?php echo Junten::radio("_order", "ASC", $prefills->_order == "ASC");
            echo Junten::label("_order_ASC", "&uarr;");
            echo Junten::radio("_order", "DESC", $prefills->_order == "DESC");
            echo Junten::label("_order_DESC", "&darr;");
        ?>',
						"twig_open" => '<?php echo Junten::open($role . "/" . $form["action"], $form["html"]); ?>',
						"twig_close" => '<?php echo Junten::close(); ?>',
						"twig_export" => '<?php echo Junten::select("_export", array(
                "" => "recherche",
                "Excel2007" => "XLSX",
                "Excel5" => "XLS"), $prefills->_export); ?>',
						"twig_submit" => '<?php echo Junten::submit("submit", "' . I18n::get("valider") . '"); ?>'
				)),
				"><view" => array($this->object_name(), array(
						"role" => Junten::$theme,
						"form" => array(
								"action" => strtolower($this->object_name()) . "/liste",
								"html" => array(
										"name" => "frm_" . $this->object_name(),
										"method" => "GET"
								)
						),
						"fields" => $fields,
						"prefills" => $prefills))
		));
	
		return $this;
	}
	
	private function edit($inputs) {
		$related = array();
		if (count($inputs) > 0) {
			foreach ($inputs as $k => $v) {
				if (isset($this->fields[$k])) {
					$this->$k = isset($inputs[$k]) ? $inputs[$k] : NULL;
				} elseif (is_array($v)) {
					foreach ($v as $k_v => $v_v) {
						if (isset($this->_has_many[$k_v]) && isset($this->_has_many[$k_v]["through"])) {
							$related[$k_v] = $v_v;
						}
					}
				}
			}
			$this->save();
			$this->reload();
			if (count($related) > 0) {
				foreach ($related as $k_rel => $v_rel) {
					$this->add($k_rel, $v_rel);
				}
			}
			return $this;
		}
		return FALSE;
	}
	
	public static function pagination($current_page, $total_pages) {
		$navnum = 6;
		if ($total_pages <= $navnum) {
			$begin = 1;
			$end = $total_pages;
		} elseif ($current_page > ceil($navnum / 2) && $current_page < $total_pages - ceil($navnum / 2)) {
			$begin = $current_page - ceil($navnum / 2) + 1;
			$end = $current_page + ceil($navnum / 2) - 1;
		} elseif ($current_page <= ceil($navnum / 2)) {
			$begin = 1;
			$end = $navnum;
		} elseif ($current_page >= $total_pages - ceil($navnum / 2)) {
			$begin = $total_pages - $navnum + 1;
			$end = $total_pages;
		}
		return (object) array(
				"start" => $begin,
				"end" => $end
		);
	}
	
	public function alias($name, $components = NULL) {
		if ($components) {
			$last = array_pop(Junten::$map->data);
			$last["><build"][2] = array_merge($last["><build"][2], $components);
			Junten::$map->data[] = $last;
		}
		Junten::$map->aliases[$this->lastfill] = $name;
		return $this;
	}
	
	public static function matches(Validation $valid, $field, $value) {
		/* if(Auth::instance()->logged_in("admin")) //may be notify the account owner
		 return TRUE; */
		if (Auth::instance()->get_user()->password == Auth::instance()->hash($value))
			return TRUE;
		$valid->error($field, Junten::message("database", "action_not_allowed"));
		return FALSE;
	}

}

?>

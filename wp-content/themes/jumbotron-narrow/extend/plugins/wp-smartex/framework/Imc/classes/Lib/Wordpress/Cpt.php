<?php
/**
 * 
 * @author landry
 * 
 * CPT stands for Custom Post Type in wordpress
 * 
 * It is used to create ORM style class from registered post type
 *
 */
class Lib_Wordpress_Cpt {
	
	protected $_has_many = array();
	protected $_belongs_to = array();
	
	private $offset = 0;
	private $limit = 0;
	private $orderby = 'date';
	private $category = 0;
	private $order = 'DESC';
	private $exclude = array();
	public $include = array();
	private $meta_value = '';
	
	/**
	 *
	 * @var WP_Post
	 */
	public $post;
	private $metas = array();
	
	private function setup_ORM($post=NULL) {
		$this->post = $post;
	
		if($this->post) {
			$this->metas = get_post_meta($this->post->ID, wp_basename(LITHIUM_PLUGIN_ROOT).".".strtolower($this->object_name()), true);
		}
	
		$ar = $this->_has_many;
		foreach($ar as $a => $b) {
			$this->$a = Lim::factory($b["model"])->where($b["foreign_key"], "=", $this->post->ID);
		}
	
		$ar = $this->_belongs_to;
		foreach ($ar as $a => $b) {
			$this->$a = Lim::factory($b["model"])->where($b["foreign_key"], "=", $this->post->ID);
		}
	}
	
	protected function object_name() {
		$class_name = get_class($this);
		return str_replace("Model_", "", $class_name);
	}
	
	protected function loaded() {
		return $this->post;
	}
	
	protected function has_many() {
		return $this->_has_many;
	}
	
	protected function belongs_to() {
		return $this->_belongs_to;
	}
	
	public function __set($name, $value) {
		$this->metas[$name] = $value;
	}
	
	public function getValue($key) {
		if(isset($this->metas[$key]))
			return $this->metas[$key];
		return;
	}
	
	public function save() {
		update_post_meta($this->post->ID, wp_basename(LITHIUM_PLUGIN_ROOT).".".strtolower($this->object_name()), $this->metas);
	}
	
	public function __call($name, $args) {
		echo $name;
		exit();
	}
	
	protected function reload() {
	
	}
	
	public function as_array() {
		return $this->metas;
	}
	
	protected function reset($clean) {
		return $this;
	}
	
	public function count_all() {
		return wp_count_posts(strtolower($this->object_name()))->publish;
	}
	
	protected function offset($number = 0) {
		$this->offset = $number;
		return $this;
	}
	
	protected function limit($number = 0) {
		$this->limit = $number;
		return $this;
	}
	
	public function find_all() {
		
		$posts = get_posts(array(
				'numberposts' => $this->limit, 'offset' => $this->offset,
				'category' => $this->category, 'orderby' => $this->orderby,
				'order' => $this->order, 'include' => $this->include,
				'exclude' => $this->exclude, 'meta_key' => wp_basename(LITHIUM_PLUGIN_ROOT).".".strtolower($this->object_name()),
				'meta_value' => $this->meta_value, 'post_type' => strtolower($this->object_name())
		));
		
		$ret = array();
		foreach ($posts as $post) {
			$ret[] = self::factory(strtolower($this->object_name()), $post);
		}
		return $ret;
	}
	
	public function pk() {
		return $this->post->ID;
	}
	

	/**
	 *
	 * @param String $name
	 * @param WP_Post $id
	 * @return Lib_Wordpress_Model
	 */
	public function factory($name, $id=NULL) {
		$class_name = "Model_" . ucfirst($name);
		return new $class_name($id);
	}
	
	public function __construct($id = NULL) {
		$this->setup_ORM($id);
	}
	
}

<?php
class Lib_Wordpress_Response {
	private $_view;
	
	/**
	 * Inserts a wordpress metabox at page or post editing or creation level
	 *
	 * @param Liv $view
	 *        	: would contain the key title in the parameters'array
	 * @param string $context
	 *        	: post or page
	 */
	public function metabox(Liv $view, $screen = null) {
		$this->_view = $view;
		add_meta_box ( $this->_view->get ( "id" ), $this->_view->get ( "title" ), array (
				$this,
				"process" 
		), $screen );
	}
	public function page(Liv $view, $role = "activate_plugins") {
		$this->_view = $view;
		add_menu_page ( $this->_view->get ( "title" ), $this->_view->get ( "menu" ), $role, $this->_view->get ( "id" ), array (
				$this,
				"process" 
		) );
	}
	public function process() {
		echo $this->_view->render ();
	}
}
?>

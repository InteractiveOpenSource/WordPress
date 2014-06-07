<?php
class Controller_Theme extends Lic {
	public function __construct($id = NULL) {
		parent::__construct ( $id );
		add_theme_support ( "post-thumbnails" );
		add_theme_support ( "widgets" );
	}
}

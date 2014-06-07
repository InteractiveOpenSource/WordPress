<?php
class Lib_Wordpress_Route {
	private static $_routes = array ();
	public static function all() {
		self::$_routes = array_unique ( self::$_routes );
		return self::$_routes;
	}
	public static function set($route) {
		self::$_routes [] = $route;
	}
}

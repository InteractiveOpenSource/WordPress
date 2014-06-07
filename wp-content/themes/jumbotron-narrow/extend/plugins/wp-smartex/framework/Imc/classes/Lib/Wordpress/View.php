<?php
class Lib_Wordpress_View {
	private $_view, $_params;
	public function __construct($path, $params) {
		$this->_view = $path;
		$this->_params = $params;
		if (! isset ( $params ["id"] ))
			$this->_params ["id"] = $this->_view;
	}
	public static function factory($view, $params = null) {
		return new Liv ( $view, $params );
	}
	public function render() {
		extract($this->_params, EXTR_SKIP);
		// Capture the view output
		ob_start();
		
		try
		{
			// Load the view within the current scope
			include LITHIUM_PLUGIN_ROOT . "/application/views/$this->_view.php";
		}
		catch (Exception $e)
		{
			// Delete the output buffer
			ob_end_clean();
		
			// Re-throw the exception
			throw $e;
		}
		
		// Get the captured output and close the buffer
		return ob_get_clean();
	}
	public function get($key) {
		if (isset ( $this->_params [$key] ))
			return $this->_params [$key];
		return;
	}
}
?>

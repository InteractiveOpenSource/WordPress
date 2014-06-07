<?php

class HTML {
	public static $windowed_urls = FALSE;
	
	public function __call($name, $args) {
		echo $name . " tsy hitaeee";
		exit;
	}
	
	public static function __callstatic($name, $args) {
		echo $name . " tsy hita ko sti";
		exit;
	}
	
	/**
	 * Creates a style sheet link element.
	 *
	 *     echo HTML::style('media/css/screen.css');
	 *
	 * @param   string  $file       file name
	 * @param   array   $attributes default attributes
	 * @param   mixed   $protocol   protocol to pass to URL::base()
	 * @param   boolean $index      include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function style($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		if (strpos($file, '://') === FALSE)
		{
			// Add the base URL
			$file = Junten::site($file, $protocol, $index);
		}
	
		// Set the stylesheet link
		$attributes['href'] = $file;
	
		// Set the stylesheet rel
		$attributes['rel'] = empty($attributes['rel']) ? 'stylesheet' : $attributes['rel'];
	
		// Set the stylesheet type
		$attributes['type'] = 'text/css';
	
		return '<link'.Junten::attributes($attributes).' />';
	}
	
	
	
	/**
	 * Creates a script link.
	 *
	 *     echo HTML::script('media/js/jquery.min.js');
	 *
	 * @param   string  $file       file name
	 * @param   array   $attributes default attributes
	 * @param   mixed   $protocol   protocol to pass to URL::base()
	 * @param   boolean $index      include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function script($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		if (strpos($file, '://') === FALSE)
		{
			// Add the base URL
			$file = Junten::site($file, $protocol, $index);
		}
	
		// Set the script link
		$attributes['src'] = $file;
	
		// Set the script type
		$attributes['type'] = 'text/javascript';
	
		return '<script'.Junten::attributes($attributes).'></script>';
	}
	
	/**
	 * Create HTML link anchors. Note that the title is not escaped, to allow
	 * HTML elements within links (images, etc).
	 *
	 *     echo HTML::anchor('/user/profile', 'My Profile');
	 *
	 * @param   string  $uri        URL or URI string
	 * @param   string  $title      link text
	 * @param   array   $attributes HTML anchor attributes
	 * @param   mixed   $protocol   protocol to pass to URL::base()
	 * @param   boolean $index      include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    URL::site
	 * @uses    HTML::attributes
	 */
	public static function anchor($uri, $title = NULL, array $attributes = NULL, $protocol = NULL, $index = TRUE)
	{
		if ($title === NULL)
		{
			// Use the URI as the title
			$title = $uri;
		}
	
		if ($uri === '')
		{
			// Only use the base URL
			$uri = Junten::base($protocol, $index);
		}
		else
		{
			if (strpos($uri, '://') !== FALSE)
			{
				if (HTML::$windowed_urls === TRUE AND empty($attributes['target']))
				{
					// Make the link open in a new window
					$attributes['target'] = '_blank';
				}
			}
			elseif ($uri[0] !== '#')
			{
				// Make the URI absolute for non-id anchors
				$uri = Junten::site($uri, $protocol, $index);
			}
		}
	
		// Add the sanitized link to the attributes
		$attributes['href'] = $uri;
	
		return '<a'.Junten::attributes($attributes).'>'.$title.'</a>';
	}
}

<?php
class Junten {
	/**
	 * 
	 * @var Map_Admin
	 */
	public static $map;
	/**
	 *
	 * @var Lib_Controller
	 */
	public static $controller;
	public static $theme;
	public static $models = array();
	public static $online = FALSE;
	private static $messages = array ();
	public static $attribute_order = array
	(
			'action',
			'method',
			'type',
			'id',
			'name',
			'value',
			'href',
			'src',
			'width',
			'height',
			'cols',
			'rows',
			'size',
			'maxlength',
			'rel',
			'media',
			'accept-charset',
			'accept',
			'tabindex',
			'accesskey',
			'alt',
			'title',
			'class',
			'style',
			'selected',
			'checked',
			'readonly',
			'disabled',
	);
	
	/**
	 * @var  boolean  use strict XHTML mode?
	*/
	public static $strict = TRUE;
	public static function encode_date($date) {
		return mysql2date ( get_option ( "links_updated_date_format" ), $date );
	}
	public static function open($action = NULL, array $attributes = NULL) {
		if ($action instanceof Request) {
			// Use the current URI
			$action = $action->uri ();
		}
		
		if (! $action) {
			// Allow empty form actions (submits back to the current url).
			$action = '';
		} elseif (strpos ( $action, '://' ) === FALSE) {
			// Make the URI absolute
			$action = site_url( $action );
		}
		
		// Add the form action to the attributes
		$attributes ['action'] = $action;
		
		// Only accept the default character set
		$attributes ['accept-charset'] = get_bloginfo("charset");
		
		if (! isset ( $attributes ['method'] )) {
			// Use POST method
			$attributes ['method'] = 'post';
		}
		
		return '<form' . self::attributes ( $attributes ) . '>';
	}
	public static function __callstatic($name, $args) {
		echo "tsis i " . $name;
		exit ();
	}
	public static function message($file = NULL, $key = NULL) {
		if (! $file && ! $key) {
			self::$messages = get_transient ( wp_basename ( LITHIUM_PLUGIN_ROOT ) );
			
			if (! empty ( self::$messages )) {
				foreach ( self::$messages as $message ) {
					if ($message != '')
						echo "<div class='updated'><p>$message</p></div>";
				}
				self::$messages = array ();
				delete_transient ( wp_basename ( LITHIUM_PLUGIN_ROOT ) );
			}
			return;
		}
		
		if (file_exists ( LITHIUM_PLUGIN_ROOT . '/application/messages/' . $file . '.php' )) {
			$messages = include LITHIUM_PLUGIN_ROOT . '/application/messages/' . $file . '.php';
			self::$messages [] = __ ( $messages [$key] );
		} else {
			self::$messages [] = $key;
		}
		set_transient ( wp_basename ( LITHIUM_PLUGIN_ROOT ), self::$messages );
	}
	public static function hidden($key, $value) {
		return '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';
	}
	public static function attributes(array $attributes = NULL) {
		if (empty ( $attributes ))
			return '';
		
		$sorted = array ();
		foreach ( self::$attribute_order as $key ) {
			if (isset ( $attributes [$key] )) {
				// Add the attribute to the sorted list
				$sorted [$key] = $attributes [$key];
			}
		}
		
		// Combine the sorted attributes
		$attributes = $sorted + $attributes;
		
		$compiled = '';
		foreach ( $attributes as $key => $value ) {
			if ($value === NULL) {
				// Skip attributes that have NULL values
				continue;
			}
			
			if (is_int ( $key )) {
				// Assume non-associative keys are mirrored attributes
				$key = $value;
				
				if (! self::$strict) {
					// Just use a key
					$value = FALSE;
				}
			}
			
			// Add the attribute key
			$compiled .= ' ' . $key;
			
			if ($value or self::$strict) {
				// Add the attribute value
				$compiled .= '="' . self::chars ( $value ) . '"';
			}
		}
		
		return $compiled;
	}
	public static function chars($value, $double_encode = TRUE)
	{
		return htmlspecialchars( (string) $value, ENT_QUOTES, get_bloginfo("charset"), $double_encode);
	}
	public static function label($input, $text = NULL, array $attributes = NULL)
	{
		if ($text === NULL)
		{
			// Use the input name as the text
			$text = ucwords(preg_replace('/[\W_]+/', ' ', $input));
		}
	
		// Set the label target
		$attributes['for'] = $input;
	
		return '<label'.self::attributes($attributes).'>'.$text.'</label>';
	}
	/**
	 * Creates a form input. If no type is specified, a "text" type input will
	 * be returned.
	 *
	 *     echo Form::input('username', $username);
	 *
	 * @param   string  $name       input name
	 * @param   string  $value      input value
	 * @param   array   $attributes html attributes
	 * @return  string
	 * @uses    HTML::attributes
	 */
	public static function input($name, $value = NULL, array $attributes = NULL)
	{		
		// Set the input name
		$attributes['name'] = $name;
	
		// Set the input value
		$attributes['value'] = $value;
	
		if ( ! isset($attributes['type']))
		{
			// Default type is text
			$attributes['type'] = 'text';
		}
	
		return '<input'.self::attributes($attributes).' />';
	}
	/**
	 * Creates the closing form tag.
	 *
	 *     echo Form::close();
	 *
	 * @return  string
	 */
	public static function close()
	{
		return '</form>';
	}
	/**
	 * Creates a submit form input.
	 *
	 *     echo Form::submit(NULL, 'Login');
	 *
	 * @param   string  $name       input name
	 * @param   string  $value      input value
	 * @param   array   $attributes html attributes
	 * @return  string
	 * @uses    Form::input
	 */
	public static function submit($name, $value, array $attributes = NULL)
	{
		$attributes['type'] = 'submit';
	
		return self::input($name, $value, $attributes);
	}
	
	public static function site($path = NULL) {
		if(!$path)
			return site_url();
		return site_url($path);
	}
	
	public static function base() {
		return site_url();
	}
	
	public static function plural($pattern, $number) {
        if($number > 1) {
            return preg_replace_callback("/[a-zA-Z]+/i", function($matches){
                if(substr($matches[0], -1)!="s")
                    return $matches[0] . "s";
                return $matches[0];
            }, sprintf($pattern, $number));
        }
        return sprintf($pattern, $number);
    }
}

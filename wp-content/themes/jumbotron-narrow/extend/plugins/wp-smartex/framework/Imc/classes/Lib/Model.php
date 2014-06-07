<?php
final class Lib_Model
{
  private $_props;

  public function __construct($props = array()) {
    $this->_props = $props;
  }

  public function getProp($name) {
    if(isset($this->_props[$name]))
      return $this->_props[$name];
    return;
  }

  public function hasProp($name) {
    return isset($this->_props[$name]);
  }
}
?>

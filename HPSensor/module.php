<?php

require_once(__DIR__ . "/../HPDevice.php");

class HPSensor extends HPDevice {

  public function Create() {
    parent::Create();
    $this->RegisterPropertyString("UniqueId", "");
	$this->RegisterPropertyString("description", "");
  }
  
  protected function LinearizeToDevice( $value ) {
	  return $value;
  }
  protected function LinearizeFromDevice( $value ) {
	  return $value;
  }

  protected function BasePath() {
    $id = intval($this->ReadPropertyString("UniqueId"));
    return "meter=$id";
  }

}

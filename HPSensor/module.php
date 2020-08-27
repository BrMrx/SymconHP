<?php

require_once(__DIR__ . "/../HPDevice.php");

class HPSensor extends HPDevice {

  public function Create() {
    parent::Create();
    $this->RegisterPropertyString("UniqueId", "");
	$this->RegisterPropertyString("description", "");
  }
  
  public function GetValue( string $Ident ) {
    switch ($Ident) {
      default:
        $value = GetValue(@IPS_GetObjectIDByIdent($Ident, $this->InstanceID));
        break;
    }
    return $value;
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

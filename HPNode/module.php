<?php

require_once(__DIR__ . "/../HPDevice.php");

class HPNode extends HPDevice {

  public function Create() {
    parent::Create();
    $this->RegisterPropertyString("productName", "");
    $this->RegisterPropertyInteger("Lin25", 25); // 
    $this->RegisterPropertyInteger("Lin50", 50); // 
    $this->RegisterPropertyInteger("Lin75", 75); // 
    $this->RegisterPropertyInteger("NodeFeatures", 0); // 
    $this->RegisterPropertyString("UniqueId", "");
	$this->RegisterPropertyString("description", "");
  }
  
  protected function GetLinearisation() {
	  $data = array();
	  
	  $lin25 = $this->ReadPropertyInteger("Lin25");
	  $lin50 = $this->ReadPropertyInteger("Lin50");
	  $lin75 = $this->ReadPropertyInteger("Lin75");
	  // Sichheitscheck auf gültige Werte
	  if( $lin25 > 0 && $lin50 > $lin25 && $lin75 > $lin50 && $lin75 < 100 ) {
		  $data[25] = $lin25;
		  $data[50] = $lin50;
		  $data[75] = $lin75;
	  }
	  $data[100] = 100;
	  return $data;
  }
  
  protected function Linearize($x,$x0,$y0,$x1,$y1) {
	  $div = $x1 - $x0;
	  return intval($y0*($x1-$x)/$div + $y1 * ($x-$x0)/$div +0.5);
  }
  
  protected function LinearizeToDevice( $value ) {
	  if( $value <= 0 )
		  return $value;
	  if( $value >= 100 )
		  return $value;
	  
	  $data = $this->GetLinearisation();
	  $x0=0;
	  $y0=0;
	  foreach ($data as $x1 => $y1) {
		  if($value > $x1) {
			 $x0 = $x1; 
			 $y0 = $y1; 
		  }
		  else {
			  return $this->Linearize($value,$x0,$y0,$x1,$y1);
		  }
	  }
	  
	  return $value;
  }
  protected function LinearizeFromDevice( $value ) {
	  if( $value <= 0 )
		  return $value;
	  if( $value >= 100 )
		  return $value;
	  
	  $data = $this->GetLinearisation();
	  $x0=0;
	  $y0=0;
	  foreach ($data as $y1 => $x1) {
		  if($value > $x1) {
			 $x0 = $x1; 
			 $y0 = $y1; 
		  }
		  else {
			  return $this->Linearize($value,$x0,$y0,$x1,$y1);
		  }
	  }
	  
	  return $value;
  }

  protected function BasePath() {
    $id = $this->ReadPropertyInteger("UniqueId");
    return "device=$id";
  }

}

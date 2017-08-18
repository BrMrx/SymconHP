<?php

abstract class HPDevice extends IPSModule {

  public function __construct($InstanceID) {
    parent::__construct($InstanceID);
  }

  public function Create() {

     if (!IPS_VariableProfileExists('CommandCtrl.HP')) 
		 IPS_CreateVariableProfile('CommandCtrl.HP', 1);
    IPS_SetVariableProfileAssociation('CommandCtrl.HP', -3,  ' << ', '', 0x000000);
    IPS_SetVariableProfileAssociation('CommandCtrl.HP', -2,  'Stop', '', 0x000000);
    IPS_SetVariableProfileAssociation('CommandCtrl.HP', -1,  ' >> ', '', 0x000000);
    IPS_SetVariableProfileAssociation('CommandCtrl.HP', 0,   '0%',   '', 0x000000);
    IPS_SetVariableProfileAssociation('CommandCtrl.HP', 25,  '25%',  '', 0x000000);
    IPS_SetVariableProfileAssociation('CommandCtrl.HP', 50,  '50%',  '', 0x000000);
    IPS_SetVariableProfileAssociation('CommandCtrl.HP', 75,  '75%',  '', 0x000000);
    IPS_SetVariableProfileAssociation('CommandCtrl.HP', 100, '100%', '', 0x000000);
    IPS_SetVariableProfileIcon('CommandCtrl.HP', 'Shutter');
	
     if (!IPS_VariableProfileExists('TemperaturCtrl.HP')) 
          IPS_CreateVariableProfile('TemperaturCtrl.HP', 2);
     IPS_SetVariableProfileValues('TemperaturCtrl.HP', 4, 40, 0.5 );
     IPS_SetVariableProfileDigits('TemperaturCtrl.HP', 1 );
     IPS_SetVariableProfileIcon('TemperaturCtrl.HP', 'Temperature');
     IPS_SetVariableProfileText('TemperaturCtrl.HP','', '°C' );

     if (!IPS_VariableProfileExists('SmokeSensor.HP')) 
		 IPS_CreateVariableProfile('SmokeSensor.HP', 0);
	 
    IPS_SetVariableProfileAssociation('SmokeSensor.HP', 0,  'nicht erkannt','', -1 );
    IPS_SetVariableProfileAssociation('SmokeSensor.HP', 1,  'erkannt',  	'Flame', 0xFF0000);
	IPS_SetVariableProfileIcon('SmokeSensor.HP', 'Fog');

	
    parent::Create();
  }

  protected function GetBridge() {
    $instance = IPS_GetInstance($this->InstanceID);
    return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
  }

  abstract protected function BasePath();
  abstract protected function LinearizeToDevice( $value );
  abstract protected function LinearizeFromDevice( $value );

  public function ApplyChanges() {
    parent::ApplyChanges();
    $this->ConnectParent("{51F4E4C4-1316-4E2F-A56E-3908FCE3F0C2}");
  }

  public function ApplyJsonData(string $jsonString) {
	  
	  $aData = json_decode($jsonString);
	  if( !$aData ) {
	    throw new Exception("Invalid JSON Data '$jsonString'");
	  }
	  
    $data = (array)$aData;
	  
    $data = (array)$data;
    $values = (array)@$data['statusesMap'];

    // Status
    if ( $this->ReadPropertyString("UniqueId") == '') {
      $this->SetStatus(104);
      return false;
    } else {
      $this->SetStatus(102);
    }

	// wenn der Status nicht gültig ist -> Knoten nicht erreichbar
	$statusValid = $data['statusValid'];
	if( $statusValid == 1 )
      $this->SetStatus(102);
	else
      $this->SetStatus(201);
	
    $dirty = false;
	

    /*
     * Properties
     */

    $name = utf8_decode((string)$data['name']);
	
    if (IPS_GetName($this->InstanceID) != $name) {
      IPS_SetName($this->InstanceID, $name);
      $dirty = true;
    }
	
    $description = utf8_decode((string)$data['description']);
    if (IPS_GetProperty($this->InstanceID, 'description') != $description) {
        IPS_SetProperty($this->InstanceID, 'description', $description);
        $dirty = true;
    }

	 $nodeFeatures = 0;

	if (get_class($this) == 'HPNode')
	{
		  $productName = utf8_decode((string)$data['productName']);
		  if (IPS_GetProperty($this->InstanceID, 'productName') != $productName) {
			IPS_SetProperty($this->InstanceID, 'productName', $productName);
			$dirty = true;
		  }

		  // der Raumthermostat heißt: "Schaltaktor DuoFern Raumthermostat" und muss deshalb vor dem Schaltaktor gefunden werden
		  $typeList = array (
						10 => "Schaltaktor DuoFern Raumthermostat",
		  				11 => "DuoFern Raumthermostat",
						0 => "Schaltaktor",
						1 => "RolloTron",
						2 => "Dimmer",
						3 => "Rohrmotoraktor Umweltsensor",
						4 => "Rohrmotoraktor",
						5 => "Rohrmotor",
						6 => "SX5",
						7 => "Connect-Aktor",
						8 => "RolloTube",
						9 => "Universal-Aktor"
						);
		
		 $nodeFeatures = 0;
		 $lbDefault=true;
		 foreach ($typeList as $typeId => $typeKeword) 
		 {
			$lPos = strpos($productName, $typeKeword );
			if( $lPos === false )
				continue;
			
			$nodeFeatures = $typeId;
			$lbDefault=false;
			break;
		 }
		 
		 if( $lbDefault )
		 {
     		IPS_LogMessage("SymconHP", "unbekannter Typ '".$productName."' -> Standardbehandlung als Schalter");
		 }


		if (IPS_GetProperty($this->InstanceID, 'NodeFeatures') != $nodeFeatures) {
		  IPS_SetProperty($this->InstanceID, 'NodeFeatures', $nodeFeatures);
		  $dirty = true;
		}
	}

    if ($dirty) 
		IPS_ApplyChanges($this->InstanceID);


	// Bei Knoten Status, Position anlegen/setzen
	if (get_class($this) == 'HPNode')
	{
		$position = $this->LinearizeFromDevice( $values['Position'] );

		switch($nodeFeatures )
		{
			case 0: //  "Schaltaktor"
			case 9: //  Universal-Aktor
			case 11: //  Raumthermostat Relais
				$switch = ($position != 0);
				if (!$valuesId = @$this->GetIDForIdent("SWITCH")) {
					$valuesId = $this->RegisterVariableBoolean("SWITCH", "Schalter", "~Switch", 1);
			        $this->EnableAction("SWITCH");
					SetValueBoolean( $valuesId, $switch );
				}
				else if( GetValueBoolean( $valuesId ) != $switch ){
					SetValueBoolean( $valuesId, $switch );
				}
			break;
			
			case 1: //  "RolloTron"
			case 3: //  "Rohrmotoraktor Umweltsensor"
			case 4: //  "Rohrmotoraktor"
			case 5: //  "Rohrmotor"
			case 6: //  "SX5 Garagentor Stellmotor"
			case 7: //  "Connect-Aktor"
			case 8: //  "RolloTube"
				$shutter = ($position != 0);
				if (!$valuesId = @$this->GetIDForIdent("SHUTTER")) {
					$valuesId = $this->RegisterVariableBoolean("SHUTTER", "Zustand", "~ShutterMove", 1);
			        $this->EnableAction("SHUTTER");
					SetValueBoolean( $valuesId, $shutter );
				}
				else if( GetValueBoolean( $valuesId ) != $shutter ){
					SetValueBoolean( $valuesId, $shutter );
				}
				
				$shutterpos = $position;
				if (!$valuesId = @$this->GetIDForIdent("SHUTTERPOS")) {
					$valuesId = $this->RegisterVariableInteger("SHUTTERPOS", "Position", "~Shutter", 2);
			        $this->EnableAction("SHUTTERPOS");
					SetValueInteger( $valuesId, $shutterpos );
				}
				else if( GetValueInteger( $valuesId ) != $shutterpos ){
					SetValueInteger( $valuesId, $shutterpos );
				}

				$cmdpos = $this->RoundTo25Percent($position);
				if (!$valuesId = @$this->GetIDForIdent("SHUTTERCMD")) {
					$valuesId = $this->RegisterVariableInteger("SHUTTERCMD", "Steuerung", "CommandCtrl.HP", 3);
			        $this->EnableAction("SHUTTERCMD");
					SetValueInteger( $valuesId, $cmdpos );
				}
				else if( GetValueInteger( $valuesId ) != $cmdpos ){
					SetValueInteger( $valuesId, $cmdpos );
				}

				$automatik = ($values['Manuellbetrieb'] == 0);
				if (!$valuesId = @$this->GetIDForIdent("AUTOMATIC")) {
					$valuesId = $this->RegisterVariableBoolean("AUTOMATIC", "Automatik", "~Switch", 10);
					$this->EnableAction("AUTOMATIC");
					SetValueBoolean( $valuesId, $automatik );
					if( $automatik )
						IPS_SetIcon($valuesId, 'Electricity');
					else
						IPS_SetIcon($valuesId, 'Execute');
				}
				else if( GetValueBoolean( $valuesId ) != $automatik ){
					SetValueBoolean( $valuesId, $automatik );
					if( $automatik )
						IPS_SetIcon($valuesId, 'Electricity');
					else
						IPS_SetIcon($valuesId, 'Execute');
				}
			break;

			case 10: //  "Raumthermostat"
				$desttemperatur = $position / 10.;
				if (!$valuesId = @$this->GetIDForIdent("DESTTEMP")) {
					$valuesId = $this->RegisterVariableFloat("DESTTEMP", "Solltemperatur", "TemperaturCtrl.HP", 1);
			        $this->EnableAction("DESTTEMP");
					SetValueFloat( $valuesId, $desttemperatur );
				}
				else if( GetValueFloat( $valuesId ) != $desttemperatur ){
					SetValueFloat( $valuesId, $desttemperatur );
				}
				
				$lRelaisStatus = ($values['relaisstatus'] != 0);
				if (!$valuesId = @$this->GetIDForIdent("RELAISSTATE")) {
					$valuesId = $this->RegisterVariableBoolean("RELAISSTATE", "Relaisstatus", "~Switch", 10);
					SetValueBoolean( $valuesId, $lRelaisStatus );
				}
				else if( GetValueBoolean( $valuesId ) != $lRelaisStatus ){
					SetValueBoolean( $valuesId, $lRelaisStatus );
				}

			break;
			
			case 2: //  "Dimmer"
				$dimmerstate = ($position != 0);
				if (!$valuesId = @$this->GetIDForIdent("DIMMERSTATE")) {
					$valuesId = $this->RegisterVariableBoolean("DIMMERSTATE", "Zustand", "~Switch", 1);
			        $this->EnableAction("DIMMERSTATE");
					SetValueBoolean( $valuesId, $dimmerstate );
				}
				else if( GetValueBoolean( $valuesId ) != $dimmerstate ){
					SetValueBoolean( $valuesId, $dimmerstate );
				}
				
				$dimmerpos = $position;
				if (!$valuesId = @$this->GetIDForIdent("DIMMERPOS")) {
					$valuesId = $this->RegisterVariableInteger("DIMMERPOS", "Helligkeit", "~Intensity.100", 2);
			        $this->EnableAction("DIMMERPOS");
					SetValueInteger( $valuesId, $dimmerpos );
				}
				else if( GetValueInteger( $valuesId ) != $dimmerpos ){
					SetValueInteger( $valuesId, $dimmerpos );
				}
				
				$cmdpos = $this->RoundTo25Percent($position);
				if (!$valuesId = @$this->GetIDForIdent("DIMMERCMD")) {
					$valuesId = $this->RegisterVariableInteger("DIMMERCMD", "Steuerung", "CommandCtrl.HP", 3);
			        $this->EnableAction("DIMMERCMD");
					SetValueInteger( $valuesId, $cmdpos );
				}
				else if( GetValueInteger( $valuesId ) != $cmdpos ){
					SetValueInteger( $valuesId, $cmdpos );
				}
			break;

		}
	}
	
	// ------- hier werden die Sensordaten ausgewertet --------------------------
	if( isset($data['data']) )
	{
		$dataValues = $data['data'];
		
       foreach ($dataValues as $sensorValue) {
		    $sensorArray = (array)$sensorValue;

		    foreach ($sensorArray as $sKey => $sValue) {

				switch($sKey)
				{
					// Sonnensensor
					case 'Sonne':
						$sun = ($sValue != "Nicht erkannt");
						
						if (!$valuesId = @$this->GetIDForIdent("SUN")) {
							$valuesId = $this->RegisterVariableBoolean("SUN", "Sonne", "~Presence", 1);
							SetValueBoolean( $valuesId, $sun );
							IPS_SetIcon($valuesId, 'Sun');

						}
						else if( GetValueBoolean( $valuesId ) != $sun ){
							SetValueBoolean( $valuesId, $sun );
							IPS_SetIcon($valuesId, 'Sun');

						}
					break;
					
					// Regensensor
					case 'Regen':
						$rain = ($sValue != "Nicht erkannt");
						
						if (!$valuesId = @$this->GetIDForIdent("RAIN")) {
							$valuesId = $this->RegisterVariableBoolean("RAIN", "Regen", "~Raining", 1);
							SetValueBoolean( $valuesId, $rain );
						}
						else if( GetValueBoolean( $valuesId ) != $rain ){
							SetValueBoolean( $valuesId, $rain );
						}
					break;
					// Lichtwert (Umweltsensor) min alle 10min aktualisieren
					case 'Lichtwert':
						$lux = floatval(str_replace( ',','.',$sValue));
						
						if (!$valuesId = @$this->GetIDForIdent("LUX")) {
							$valuesId = $this->RegisterVariableFloat("LUX", "Lichtwert", "~Illumination.F", 2);
							SetValueFloat( $valuesId, $lux );
						}
						else if( GetValueFloat( $valuesId ) != $lux || $this->needsRefresh($valuesId,10*60) ){
							SetValueFloat( $valuesId, $lux );
						}
					break;

					// Lichtwert (Umweltsensor)  min alle 10min aktualisieren
					case 'Windgeschw.':
						$wind = floatval(str_replace( ',','.',$sValue));
						
						if (!$valuesId = @$this->GetIDForIdent("WIND")) {
							$valuesId = $this->RegisterVariableFloat("WIND", "Windgeschwindigkeit", "~WindSpeed.ms", 3);
							SetValueFloat( $valuesId, $wind );
						}
						else if( GetValueFloat( $valuesId ) != $wind || $this->needsRefresh($valuesId,10*60) ){
							SetValueFloat( $valuesId, $wind );
						}
					break;

					// Temperatur (Umweltsensor)  min alle 10min aktualisieren
					case 'Temperatur':
						$temperature = floatval(str_replace( ',','.',$sValue));
						
						if (!$valuesId = @$this->GetIDForIdent("TEMPERATURE")) {
							$valuesId = $this->RegisterVariableFloat("TEMPERATURE", "Temperatur", "~Temperature", 4);
							SetValueFloat( $valuesId, $temperature );
						}
						else if( GetValueFloat( $valuesId ) != $temperature || $this->needsRefresh($valuesId,10*60) ){
							SetValueFloat( $valuesId, $temperature );
						}
					break;

					// Temperatur (Umweltsensor)
					case 'Sonnenhöhe':
						$sonnenhoehe = floatval(str_replace( ',','.',$sValue));
						
						if (!$valuesId = @$this->GetIDForIdent("SUNHEIGHT")) {
							$valuesId = $this->RegisterVariableFloat("SUNHEIGHT", "Sonnenhöhe", "", 5);
							SetValueFloat( $valuesId, $sonnenhoehe );
						}
						else if( GetValueFloat( $valuesId ) != $sonnenhoehe ){
							SetValueFloat( $valuesId, $sonnenhoehe );
						}
					break;

					case 'Sonnenrichtung':
						$sonnenrichtung = floatval(preg_replace('#\D#', '', $sValue));
						
						if (!$valuesId = @$this->GetIDForIdent("SUNDIRECTION")) {
							$valuesId = $this->RegisterVariableFloat("SUNDIRECTION", "Sonnenrichtung", "", 6);
							SetValueFloat( $valuesId, $sonnenrichtung );
						}
						else if( GetValueFloat( $valuesId ) != $sonnenrichtung ){
							SetValueFloat( $valuesId, $sonnenrichtung );
						}
					break;
					
					case 'Aktualisiert':
						$akttime = $sValue;
						
						if (!$valuesId = @$this->GetIDForIdent("ACTTIME")) {
							$valuesId = $this->RegisterVariableString("ACTTIME", "Aktualisiert", "", 20);
							SetValueString( $valuesId, $akttime );
						}
						else if( GetValueString( $valuesId ) != $akttime ){
							SetValueString( $valuesId, $akttime );
						}
					break;
					
					// Bewegungssensor
					case 'Bewegung':
						$movement = ($sValue != "Nicht erkannt");
						
						if (!$valuesId = @$this->GetIDForIdent("MOTION")) {
							$valuesId = $this->RegisterVariableBoolean("MOTION", "Bewegung", "~Motion", 1);
							SetValueBoolean( $valuesId, $movement );
						}
						else if( GetValueBoolean( $valuesId ) != $movement ){
							SetValueBoolean( $valuesId, $movement );
						}
					break;

					// Produktname Sensor DuoFern Funksender UP
					case 'Schließer':
						$shutter = ($sValue == "Geöffnet");
						
						if (!$valuesId = @$this->GetIDForIdent("SHUTTER")) {
							$valuesId = $this->RegisterVariableBoolean("SHUTTER", "Schließer", "~Window", 1);
							SetValueBoolean( $valuesId, $shutter );
						}
						else if( GetValueBoolean( $valuesId ) != $shutter ){
							SetValueBoolean( $valuesId, $shutter );
						}
					break;
					
					// Produktname Sensor DuoFern Funksender UP
					case 'HomePilot-Zone':
						$zone = ($sValue == "Betreten");
						
						if (!$valuesId = @$this->GetIDForIdent("HP_ZONE")) {
							$valuesId = $this->RegisterVariableBoolean("HP_ZONE", "HomePilot-Zone", "~Presence", 1);
							SetValueBoolean( $valuesId, $zone );
						}
						else if( GetValueBoolean( $valuesId ) != $zone ){
							SetValueBoolean( $valuesId, $zone );
						}
					break;

					// Rauchsensor
					case 'Rauch':
						$smoke = ($sValue != "Nicht erkannt");
						
						if (!$valuesId = @$this->GetIDForIdent("SMOKE")) {
							$valuesId = $this->RegisterVariableBoolean("SMOKE", "Rauch", "SmokeSensor.HP", 1);
							SetValueBoolean( $valuesId, $smoke );
						}
						else if( GetValueBoolean( $valuesId ) != $smoke ){
							SetValueBoolean( $valuesId, $smoke );
						}
					break;
					
					// Batterie-Status (Rauchsensor)
					case 'Batterie-Status':
						$batterie = intval(str_replace( ',','.',$sValue));
						
						if (!$valuesId = @$this->GetIDForIdent("BATTERIE")) {
							$valuesId = $this->RegisterVariableInteger("BATTERIE", "Batterie Status", "~Intensity.100", 2);
							SetValueInteger( $valuesId, $batterie );
						}
						else if( GetValueInteger( $valuesId ) != $batterie || $this->needsRefresh($valuesId,10*60) ){
							SetValueInteger( $valuesId, $batterie );
						}
					break;
					
					// DuoFern Raumthermostat 
					case 'Aktueller Sollwert':
						$temperature = floatval(str_replace( ',','.',$sValue));
						
						if (!$valuesId = @$this->GetIDForIdent("TEMPERATUR_NOM")) {
							$valuesId = $this->RegisterVariableFloat("TEMPERATUR_NOM", "Solltemperatur", "TemperaturCtrl.HP", 5);
							SetValueFloat( $valuesId, $temperature );
						}
						else if( GetValueFloat( $valuesId ) != $temperature || $this->needsRefresh($valuesId,10*60) ){
							SetValueFloat( $valuesId, $temperature );
						}
					break;


					default:
						IPS_LogMessage("HPBridge","unknown Sensor Value '$sKey': $sValue");
					break;
					
				}
			}
	   }
	}
  }

  protected function RoundTo25Percent( $value )
  {
	  return intval(($value + 12) /25) * 25;
  }
  
   protected function needsRefresh($id,	$maxTime) {
  		$varObj = IPS_GetVariable( $id );
		$dt = time() - $varObj["VariableUpdated"];
		return $dt > $maxTime;
   }
  /*
   * HP_RequestData($id)
   * Abgleich des Status einer Lampe oder Sensor (HP_SyncStates sollte bevorzugewerden,
   * da direkt alle Lampen abgeglichen werden mit nur 1 Request zum Homepiloten)
   */
  public function RequestData() {
    $data = HP_Request($this->GetBridge(), $this->BasePath() );
	
	if($data)
	{
		$this->ApplyJsonData(json_encode($data));
	}
	else
	{
      IPS_LogMessage("SymconHP", "Es ist ein Fehler bei der Datenabfrage ".$this->BasePath()." aufgetreten");
    }
}

  //public function RequestAction( string $key, string $strValue) {
  public function RequestAction( $key, $strValue) {
 	$value =  intval($strValue);
	  
	$NewValue = 0;
	  
    switch ($key) {
      case 'SWITCH':
		if( $value == 1 )
			$NewValue = 1;
		else
			$NewValue = 0;
         break;
	  case 'AUTOMATIC':
		if( $value == 1 )
			$NewValue = 1;
		else
			$NewValue = 0;
         break;
      case 'SHUTTER':
		if( $value == 1 )
			$NewValue = 0;
		else
			$NewValue = 1;
        break;
      case 'DIMMERSTATE':
		if( $value == 1 )
			$NewValue = 1;
		else
			$NewValue = 0;
        break;
	
	  case 'DESTTEMP':
		$NewValue = floatval($strValue);
        break;
		
      case 'SHUTTERPOS':
      case 'DIMMERPOS':
      case 'SHUTTERCMD':
      case 'DIMMERCMD':
         $NewValue = $value;
         break;
       
       default:
          throw new Exception("Invalid Ident '$key'");
         
    }
	

     $this->SetValue($key, $NewValue);
   
   
  }

  /*
   * HP_GetValue($id, $key)
   * Liefert einen Deviceparameter (siehe HP_SetValue)
   */
  public function GetValue(string $key) {
    switch ($key) {
      default:
        $value = GetValue(@IPS_GetObjectIDByIdent($key, $this->InstanceID));
        break;
    }
    return $value;
  }

  /*
   * HP_SetValue($id, $key, $value)
   * Anpassung eines Deviceparameter siehe SetValues
   
 verfügbare Commands
      UP:1,
        STOP:2,
        DOWN:3,
        POSITION_0:4,
        POSITION_25:5,
        POSITION_50:6,
        POSITION_75:7,
        POSITION_100:8,
        POSITION_N:9,
        ON:10,
        OFF:11,
        INCREMENT:23,
        DECREMENT:24
        
        POS wird nur bei Kommando '9' benötigt
        bei den anderen Kommandos hat es aber keine Auswirkung
        
        
        
        mit http://homepilotip/deviceajax.do?devices=1
        bekommt man eine Deviceliste mit den Statis der Aktoren

   */
  protected function SetValue(string $key, $value) {
 	
	$uniqueId = $this->ReadPropertyString("UniqueId");
	if ( $uniqueId == '') {
      $this->SetStatus(104);
      return false;
    }
	
	
	switch ($key) {
      case 'SWITCH':
	  	if( $value == 1 )
			$cmd = 10;
		else
			$cmd = 11;

		 $path= "cid=$cmd&did=$uniqueId&command=1";
         break;
	  case 'AUTOMATIC':
	     $automatikId = 3;
  	
		if( $value == 1 )
			$cmd = "false";
		else
			$cmd = "true";
		
		$path= "automation=1&data={%22did%22:$uniqueId,%22automation%22:$automatikId,%22state%22:$cmd}";
         break;
      case 'SHUTTER':
      case 'DIMMERSTATE':
		if( $value == 1 )
			$pos = 100;
		else
			$pos = 0;
		
		$path= "cid=9&did=$uniqueId&goto=$pos&command=1";
        break;
		
	  case 'SHUTTERCMD':
			switch( $value )
			{
				case -1: // Up					break;
					$value = -3;
					break;
				case -3: // Down
					$value = -1;
					break;
			}
			// ohne break weiter !!
	  
	  case 'DIMMERCMD':
		if( $value < 0 ) {
			switch( $value )
			{
				case -1: // Up				
				case -2: // Stop
				case -3: // Down
					$cmd = -$value;
					$path= "cid=$cmd&did=$uniqueId&command=1";
					break;
				default:
					return;
			}
		}
		else {
			$value = $this->LinearizeToDevice($value);
			$path= "cid=9&did=$uniqueId&goto=$value&command=1";
		}
		break;
		
	  case 'DESTTEMP':
		$value = intval($value * 10);
		// auf 0.5er Schritte runden
		$value = intval($value /5) *5;
		if( $value < 40 )
			$value = 40;
		if( $value > 400 )
			$value = 400;
		 $path= "cid=9&did=$uniqueId&goto=$value&command=1";
         break;
	  
      case 'SHUTTERPOS':
      case 'DIMMERPOS':
		 $value = $this->LinearizeToDevice($value);
		 $path= "cid=9&did=$uniqueId&goto=$value&command=1";
         break;
    }
	
	if( isset($path)) {
//		IPS_LogMessage("SymconHP", "$path");
		HP_Request($this->GetBridge(), $path );
        IPS_Sleep(500);

		$this->RequestData();
	}

	return true;
	
  }

  /*
   * HP_SetState(integer $id, boolea $value)
   */
  public function SetState(bool $value) {
	$nodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
	
	$setVal = 0;
	if( $value )
		$setVal = 1;
	
	switch( $nodeFeatures )
	{
		case 0: //  "Schaltaktor"
		case 9: //  Universal-Aktor
		case 11: //  Raumthermostat Relais
			return $this->SetValue("SWITCH", $setVal);

		case 2: //  "Dimmer"
			return $this->SetValue("DIMMERSTATE", $setVal);
			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
			return $this->SetValue("SHUTTER", $setVal);
	}
	return false;
	  
  }

  /*
   * HP_GetState(integer $id)
   */
  public function GetState() {
	$nodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
		
	switch( $nodeFeatures )
	{
		case 0: //  "Schaltaktor"
		case 9: //  Universal-Aktor
		case 11: //  Raumthermostat Relais
			return $this->GetValue("SWITCH");

		case 2: //  "Dimmer"
			return  $this->GetValue("DIMMERSTATE");
			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
			return $this->GetValue("SHUTTER" );
	}
	return false;
  }

 /*
   * HP_SetPosition(integer $id, float $value)
   */
  public function SetPosition(float $value) {
	$nodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
	
	$lMinVal=0;
	$lMaxVal=100;
	
	switch( $nodeFeatures )
	{
		case 10:
			$lMinVal=4;
			$lMaxVal=40;
			break;
	}
	
	
	if( $value < $lMinVal )
		$value = $lMinVal;
	if( $value > $lMaxVal )
		$value = $lMaxVal;
	
	switch( $nodeFeatures )
	{
		case 0: //  "Schaltaktor"
		case 9: //  Universal-Aktor
		case 11: //  Raumthermostat Relais
			return $this->SetValue("SWITCH", intval($value) > 0);

		case 2: //  "Dimmer"
			return $this->SetValue("DIMMERPOS", intval($value));

		case 10: //  "Raumthermostat"
			return $this->SetValue("DESTTEMP", $value);
			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
			return $this->SetValue("SHUTTERPOS", intval($value) );
	}
	  
  }

  /*
   * HP_DirectionUp(integer $id)
   */
  public function DirectionUp() {
	$nodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
		
	switch( $nodeFeatures )
	{
		case 0: //  "Schaltaktor"
		case 9: //  Universal-Aktor
		case 11: //  Raumthermostat Relais
			return $this->SetValue("SWITCH", true);

		case 2: //  "Dimmer"
			return $this->SetValue("DIMMERCMD", -1);
			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
			return $this->SetValue("SHUTTERCMD", -3);
	}
	  
  }

    /*
   * HP_DirectionStop(integer $id)
   */
  public function DirectionStop() {
	$nodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
		
	switch( $nodeFeatures )
	{
		case 2: //  "Dimmer"
			return $this->SetValue("DIMMERCMD", -2);
			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
			return $this->SetValue("SHUTTERCMD", -2);
	}
  }

     /*
   * HP_DirectionDown(integer $id)
   */
  public function DirectionDown() {
	$nodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
		
	switch( $nodeFeatures )
	{
		case 0: //  "Schaltaktor"
		case 9: //  Universal-Aktor
		case 11: //  Raumthermostat Relais
			return $this->SetValue("SWITCH", false);

		case 2: //  "Dimmer"
			return $this->SetValue("DIMMERCMD", -3);
			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
			return $this->SetValue("SHUTTERCMD", -1);
	}
  }

  /*
   * HP_GetState(integer $id)
   */
  public function GetPosition() {
	$nodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
		
	switch( $nodeFeatures )
	{
		case 0: //  "Schaltaktor"
		case 9: //  Universal-Aktor
		case 11: //  Raumthermostat Relais
			if( $this->GetValue("SWITCH") > 0 )
				return 1;
			else
				return 0;

		case 2: //  "Dimmer"
			return $this->GetValue("DIMMERPOS");

		case 10: //  "Raumthermostat"
			return $this->GetValue("DESTTEMP");
			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
			return $this->GetValue("SHUTTERPOS");
	}
	return 0;
  }
 
 /*
   * HP_SetAutomatic(integer $id, boolea $value)
   */
  public function SetAutomatic(bool $value) {
	$nodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
	
	$setVal = 0;
	if( $value )
		$setVal = 1;
	
	switch( $nodeFeatures )
	{			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
			return $this->SetValue("AUTOMATIC", $setVal);
	}
	  
  }

  /*
   * HP_GetAutomatic(integer $id)
   */
  public function GetAutomatic() {
	$nodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
		
	switch( $nodeFeatures )
	{			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
			return $this->GetValue("AUTOMATIC" );
	}
	return false;
  }



 
}

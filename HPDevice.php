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
	
	if (!IPS_VariableProfileExists('WindowHP.Reversed')) 
		 IPS_CreateVariableProfile('WindowHP.Reversed', 1);
    IPS_SetVariableProfileIcon('WindowHP.Reversed', 'Window');
    IPS_SetVariableProfileAssociation('WindowHP.Reversed', 0,  'Geschlossen', '', -1 );
    IPS_SetVariableProfileAssociation('WindowHP.Reversed', 1,  'Gekippt', 	'', 0x0000FF);
    IPS_SetVariableProfileAssociation('WindowHP.Reversed', 2,  'Geöffnet', '', 0x00FF00);
	IPS_SetVariableProfileValues('WindowHP.Reversed', 0, 2, 1 );
	
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

  public function GetBridge() {
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


  public function GetProductInfoFromDeviceNumber( $ProductId )
  {
	  $typeList = array (
		'35000864' 		=> array( 'DuoFern Connect Aktor 9477',              0 ),
		'14234511' 		=> array( 'DuoFern RolloTronStandard',               1 ),
		'35000662' 		=> array( 'DuoFern Rohrmotor Aktor',                 4 ),
		'31500162' 		=> array( 'DuoFern Rohrmotorsteuerung',              5 ),
		'36500172' 		=> array( 'DuoFern TrollBasis 5615',                 13),
		'27601565' 		=> array( 'DuoFern Rohrmotor',                       5 ),
		'45059071' 		=> array( 'RolloPort SX5 DuoFern RP-SX5DF-900N-3',   6 ),
		'35000462' 		=> array( 'DuoFern Universal Dimmaktor',             2 ),
		'35140462' 		=> array( 'DuoFern UniversalDimmer 9476',            2 ),
		'10502001' 		=> array( 'Duofern Zeitschaltuhr premium smart',     12),
		'36500572' 		=> array( 'Duofern Troll Comfort 5665',              12),
		'36500572_A' 	=> array( 'Duofern Troll Comfort 5665',              12),
		'32000064' 		=> array( 'DuoFern Umweltsensor',                    3 ),
		'32000064_A' 	=> array( 'DuoFern Umweltsensor Aktor',              3 ),
		'32000064_S' 	=> array( 'DuoFern Umweltsensor Sensor',             3 ),
		'32001664' 		=> array( 'DuoFern Rauchmelder',              		 15),
		'23782076' 		=> array( 'DuoFern RollTube S-Line Sun',				 1 ),
		'16234511' 		=> array( 'DuoFern RolloTron Comfort 1800/1805/1840',1 ),
		'16234511_A' 	=> array( 'DuoFern RolloTron Comfort 1800/1805/1840',1 ),
		'14236011' 		=> array( 'DuoFern RolloTron Pro Comfort 9800',      1 ),
		'23602075' 		=> array( 'DuoFern S-Line-Motor-Typ-SLDM-10/16-PZ',  5 ),
		'35002414' 		=> array( 'Z-Wave Steckdose',                        0 ),
		'35000262' 		=> array( 'DuoFern 2 Kanal Aktor 9470-2',            0 ),
		'35001164' 		=> array( 'DuoFern Zwischenstecker Schalten 9472',   0 ),
		'32501972' 		=> array( 'DuoFern Mehrfachwandtaster 230V-9494-2',  0 ),
		'32501772' 		=> array( 'DuoFern Bewegungsmelder 9484',            11),
		'32501772_A'	=> array( 'DuoFern Bewegungsmelder 9484 Aktor',      11),
		'32501772_S'	=> array( 'DuoFern Bewegungsmelder 9484 Sensor',     11),
		'32501812' 		=> array( 'DuoFern Raumthermostat 9485',             10),
		'32501812_A' 	=> array( 'DuoFern Raumthermostat 9485 Aktor',       10),
		'32501812_S' 	=> array( 'DuoFern Raumthermostat 9485 Sensor',      10),
		'35003064' 		=> array( 'DuoFern Heizkörperstellantrieb 9433',     14),
		'99999981' 		=> array( 'Philips Hue Weiße-Lampe',                 2 ),
		'99999982' 		=> array( 'Philips Hue Ambiance-Spot',               2 ),
		'99999983' 		=> array( 'Philips Hue RGB-Lampe',                   2 ),
						);

	 if( array_key_exists ( $ProductId,  $typeList  ) )
		 return $typeList[$ProductId];
	  
	  
	 IPS_LogMessage("SymconHP", "unbekannte deviceNumber '".$ProductId."' ");
  
	 return null;
	  
  }

  public function ApplyJsonData(string $jsonString) {
	  
	  $aData = json_decode($jsonString);
	  if( !$aData ) {
	    throw new Exception("Invalid JSON Data '$jsonString'");
	  }
	  
    $data = (array)$aData;
	  
    $data = (array)$data;
	
    $values = (array)@$data['statusesMap'];

	$lNewStatus;
    // Status
    if ( $this->ReadPropertyString("UniqueId") == '') {
      if( $this->GetStatus() != 104 )
      	$this->SetStatus(104);
      return false;
    } else {
      $lNewStatus = 102;
    }

	$this->SendDebug("ApplyJsonData", json_encode($data, JSON_PRETTY_PRINT), 0);

	// wenn der Status nicht gültig ist -> Knoten nicht erreichbar
	$statusValid = $data['statusValid'];
	if( $statusValid == 1 )
      $lNewStatus = 102;
	else
      $lNewStatus = 201;
	
    $dirty = false;
	
	if( $lNewStatus != $this->GetStatus() )
	{
		$this->SetStatus($lNewStatus);
	}

    /*
     * Properties
     */

    $name = utf8_decode((string)$data['name']);
	
    if (IPS_GetName($this->InstanceID) != $name) {
      IPS_SetName($this->InstanceID, $name);
      $dirty = true;
    }
	
    $description = utf8_decode((string)$data['description']);
    $lOdDescription = IPS_GetProperty($this->InstanceID, 'description');
    if ( $lOdDescription != $description) {
        IPS_SetProperty($this->InstanceID, 'description', $description);
        $this->SendDebug("ApplyJsonData", "Instanz ".$this->InstanceID." update description '".$lOdDescription."' -> '".$description."'", 0 );
        $dirty = true;
    }


	$lBattState = false;
	if( array_key_exists("batteryLow",$data) )
	{
		if (is_bool($data['batteryLow']) === true) {
			$lBattState = $data['batteryLow'];
		} else {
			$lBattState = boolval($data['batteryLow']);
		}
		if (!$valuesId = @$this->GetIDForIdent("BATTERY_STATE")) {
			$valuesId = $this->RegisterVariableBoolean("BATTERY_STATE", "Batteriestatus", "~Battery", 10);
			SetValueBoolean( $valuesId, $lBattState );
		}
		else if( GetValueBoolean( $valuesId ) != $lBattState ){
			SetValueBoolean( $valuesId, $lBattState );
		}
	}

	if( array_key_exists("batteryStatus",$data) )
	{
		if (is_integer($data['batteryStatus']) === true) {
			$batterie = $data['batteryStatus'];
		} else {
			$batterie = intval(str_replace( ',','.',$data['batteryStatus']));
		}
				
		if (!$valuesId = @$this->GetIDForIdent("BATTERIE")) {
			$valuesId = $this->RegisterVariableInteger("BATTERIE", "Batterie Status", "~Intensity.100", 2);
			SetValueInteger( $valuesId, $batterie );
		}
		else if( GetValueInteger( $valuesId ) != $batterie || $this->needsRefresh($valuesId,10*60) ){
			SetValueInteger( $valuesId, $batterie );
		}	
		
		if( $batterie < 6 && !$lBattState )
		{
			$lBattState = true;
			if (!$valuesId = @$this->GetIDForIdent("BATTERY_STATE")) {
				$valuesId = $this->RegisterVariableBoolean("BATTERY_STATE", "Batteriestatus", "~Battery", 10);
				SetValueBoolean( $valuesId, $lBattState );
			}
			else if( GetValueBoolean( $valuesId ) != $lBattState ){
				SetValueBoolean( $valuesId, $lBattState );
			}		
		}
	}


	$nodeFeatures = 0;

	if (get_class($this) == 'HPNode')
	{
		$productName = "unknown ProductName for ";
		$nodeFeatures = 0;
		$lbDefault=true;

		if( isset( $data['productName'] ) )
		{
			$productName = utf8_decode((string)$data['productName']);
			
			// der Raumthermostat heißt: "Schaltaktor DuoFern Raumthermostat" und muss deshalb vor dem Schaltaktor gefunden werden
			$typeList = array (
							10 => "Schaltaktor DuoFern Raumthermostat",
							15 => "Raumthermostat 9485",
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
							9 => "Universal-Aktor",
							12 => "Troll Comfort",
							13 => "Troll Basis",
							14 => "Heizkörperstellantrieb",
							15 => "Rauchmelder"
							);
			
			 foreach ($typeList as $typeId => $typeKeword) 
			 {
				$lPos = strpos($productName, $typeKeword );
				if( $lPos === false )
					continue;
				
				$nodeFeatures = $typeId;
				$lbDefault=false;
				break;
			 }
		  }
		  if( isset( $data['productName'] ) )
		  {
		  	$productName = utf8_decode((string)$data['productName']);
		  	
		  	// der Raumthermostat heißt: "Schaltaktor DuoFern Raumthermostat" und muss deshalb vor dem Schaltaktor gefunden werden
		  	$typeList = array (
		  					10 => "Schaltaktor DuoFern Raumthermostat",
		  					15 => "Raumthermostat 9485",
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
		  					9 => "Universal-Aktor",
		  					12 => "Troll Comfort",
		  					13 => "Troll Basis",
		  					14 => "Heizkörperstellantrieb",
		  					15 => "Rauchmelder"
		  					);
		  	
		  	 foreach ($typeList as $typeId => $typeKeword) 
		  	 {
		  		$lPos = strpos($productName, $typeKeword );
		  		if( $lPos === false )
		  			continue;
		  		
		  		$nodeFeatures = $typeId;
		  		$lbDefault=false;
		  		break;
		  	 }
		    }
		  else if( isset( $data['deviceNumber'] ) )
		  {
		  
			  $lInfo = $this->GetProductInfoFromDeviceNumber($data['deviceNumber']);
			  if( $lInfo )
			  {
				$productName = $lInfo[0];
				$nodeFeatures = $lInfo[1];
				$lbDefault=false;
			  }			  
		  }
		  
		  $lOldproductName = IPS_GetProperty($this->InstanceID, 'productName');
		  if ( $lOldproductName != $productName) {
			IPS_SetProperty($this->InstanceID, 'productName', $productName);
			$this->SendDebug("ApplyJsonData", "Instanz ".$this->InstanceID." update productName '".$lOldproductName."' -> '".$productName."'", 0 );
			$dirty = true;
		  }

		 
		 if( $lbDefault )
		 {
     		IPS_LogMessage("SymconHP", "unbekannter Typ '".$productName."' -> Standardbehandlung als Schalter");
		 }

		$lOldNodeFeatures = IPS_GetProperty($this->InstanceID, 'NodeFeatures');
		if ( $lOldNodeFeatures != $nodeFeatures) {
		  IPS_SetProperty($this->InstanceID, 'NodeFeatures', $nodeFeatures);
		  $this->SendDebug("ApplyJsonData", "Instanz ".$this->InstanceID." update productName '".$lOldNodeFeatures."' -> '".$nodeFeatures."'", 0 );
		  $dirty = true;
		}
	}

    if ($dirty) 
    {
		$this->SendDebug("ApplyJsonData", "Instanz ".$this->InstanceID." apply changes", 0 );
		 if(IPS_HasChanges($this->InstanceID))
		 {
              IPS_ApplyChanges($this->InstanceID);
	     }
	}


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
			case 12: //  "Troll Comfort"
			case 13: //  "Troll Basis"
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

			case 14: //  "Heizkörper stellantrieb"
				$desttemperatur = $position / 10.;
				if (!$valuesId = @$this->GetIDForIdent("DESTTEMP")) {
					$valuesId = $this->RegisterVariableFloat("DESTTEMP", "Solltemperatur", "TemperaturCtrl.HP", 1);
			        $this->EnableAction("DESTTEMP");
					SetValueFloat( $valuesId, $desttemperatur );
				}
				else if( GetValueFloat( $valuesId ) != $desttemperatur ){
					SetValueFloat( $valuesId, $desttemperatur );
				}
				
				$temperature = $values['acttemperatur'] / 10.;
				
				if (!$valuesId = @$this->GetIDForIdent("TEMPERATURE")) {
					$valuesId = $this->RegisterVariableFloat("TEMPERATURE", "Temperatur", "~Temperature", 4);
					SetValueFloat( $valuesId, $temperature );
				}
				else if( GetValueFloat( $valuesId ) != $temperature ){
					SetValueFloat( $valuesId, $temperature );
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
				
				
				if( array_key_exists("acttemperatur",$values) )
				{
					$temperature = $values['acttemperatur'] / 10.;
					
					if (!$valuesId = @$this->GetIDForIdent("TEMPERATURE")) {
						$valuesId = $this->RegisterVariableFloat("TEMPERATURE", "Temperatur", "~Temperature", 4);
						SetValueFloat( $valuesId, $temperature );
					}
					else if( GetValueFloat( $valuesId ) != $temperature ){
						SetValueFloat( $valuesId, $temperature );
					}
				}				

				if( array_key_exists("Manuellbetrieb",$values) )
				{
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
		$dataValues = array();
		
		if( isset($data['readings']) )
			$dataValues[0] = (array)$data['data'];
		else
			$dataValues = $data['data'];
		
	    $this->SendDebug("ApplyJsonData", "Sensor Values:".print_r($dataValues, TRUE), 0);
		
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
					
					// Sonnensensor
					case 'sun_detected':
						$sun = ($sValue == "1");
						
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
										// Regensensor
					case 'rain_detected':
						$rain = ($sValue == "1");
						
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
					case 'sun_brightness':
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
					case 'wind_speed':
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
					case 'temperature_primary':
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
					case 'sun_elevation':
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
					
					case 'sun_direction':
						$sonnenrichtung = floatval(str_replace( ',','.',$sValue));
						
						if (!$valuesId = @$this->GetIDForIdent("SUNDIRECTION")) {
							$valuesId = $this->RegisterVariableFloat("SUNDIRECTION", "Sonnenrichtung", "", 6);
							SetValueFloat( $valuesId, $sonnenrichtung );
						}
						else if( GetValueFloat( $valuesId ) != $sonnenrichtung ){
							SetValueFloat( $valuesId, $sonnenrichtung );
						}
					break;
					
					case "timestamp":
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
					case 'movement_detected':
						$movement = ($sValue == "1");

						if (!$valuesId = @$this->GetIDForIdent("MOTION")) {
							$valuesId = $this->RegisterVariableBoolean("MOTION", "Bewegung", "~Motion", 1);
							SetValueBoolean( $valuesId, $movement );
						}
						else if( GetValueBoolean( $valuesId ) != $movement ){
							SetValueBoolean( $valuesId, $movement );
						}
					break;
					
					// Tri State Fenstersensor
					case 'contact_state':
						$shutter = 0;
						if($sValue == "tilted")
							$shutter = 1;
						else if($sValue == "open")
							$shutter = 2;
						
						if (!$valuesId = @$this->GetIDForIdent("WINDOW")) {
							$valuesId = $this->RegisterVariableInteger("WINDOW", "Schließkontakt", "WindowHP.Reversed", 1);
							SetValueInteger( $valuesId, $shutter );
						}
						else if( GetValueInteger( $valuesId ) != $shutter ){
							SetValueInteger( $valuesId, $shutter );
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

					// Rauchmelder
					case 'smoke_detected':
						if (is_bool($sValue) === true) {
							$smoke = $sValue;
						} else {
							$smoke = boolval(sValue);
						}
						
						if (!$valuesId = @$this->GetIDForIdent("SMOKE")) {
							$valuesId = $this->RegisterVariableBoolean("SMOKE", "Rauch", "SmokeSensor.HP", 1);
							SetValueBoolean( $valuesId, $smoke );
						}
						else if( GetValueBoolean( $valuesId ) != $smoke ){
							SetValueBoolean( $valuesId, $smoke );
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
					case 'temperature_target':
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
						$this->SendDebug( "ApplyJsonData","unknown Sensor Value '$sKey': $sValue", 0 );
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
  	$lBasePath = $this->BasePath();
  	
  	if( HP_ProtocolVersion($this->GetBridge()) == 5 )
  	{
  		// noch etwas dranhängen damit der single Request erkannt wird
  		$lBasePath .= "=single-request";
  	}
  
    $data = HP_Request($this->GetBridge(), $lBasePath );
	
	if($data)
	{
		$this->ApplyJsonData(json_encode($data));
	}
	else
	{
		$this->SendDebug( "RequestData", "Keine Daten bei Datenabfrage '".$lBasePath."'", 0 );
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
  
  public function GetValue( $Ident ) {
    switch ($Ident) {
      default:
        $value = GetValue(@IPS_GetObjectIDByIdent($Ident, $this->InstanceID));
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
  protected function SetValue( $key, $value) {
 	
	$uniqueId = $this->ReadPropertyString("UniqueId");
	if ( $uniqueId == '') {
      $this->SetStatus(104);
      return false;
    }
	

	switch ($key) {
      case 'SWITCH':
	  	if( $value == 1 )
		{
			$data_json = json_encode( array("name" => "TURN_ON_CMD" ) );		
			$cmd = 10;
		}
		else
		{
			$data_json = json_encode( array("name" => "TURN_OFF_CMD" ) );		
			$cmd = 11;
		}
		 $path= "cid=$cmd&did=$uniqueId&command=1";
         break;
	  case 'AUTOMATIC':
	    $automatikId = 3;
  	
		if( $value == 1 )
		{
			$data_json = json_encode( array("name" => "AUTO_MODE_CFG","value" => true ) );		
			$cmd = "false";
		}
		else
		{
			$data_json = json_encode( array("name" => "AUTO_MODE_CFG","value" => false ) );		
			$cmd = "true";
		}

		$path= "automation=1&data={%22did%22:$uniqueId,%22automation%22:$automatikId,%22state%22:$cmd}";
        break;
      case 'SHUTTER':
      case 'DIMMERSTATE':
		if( $value == 1 )
			$pos = 100;
		else
			$pos = 0;
		
		$data_json = json_encode( array("name" => "GOTO_POS_CMD","value" => $pos ) );		
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
					$data_json = json_encode( array("name" => "POS_UP_CMD" ) );		
					$cmd = -$value;
					$path= "cid=$cmd&did=$uniqueId&command=1";
					break;
				case -2: // Stop
					$data_json = json_encode( array("name" => "STOP_CMD" ) );		
					$cmd = -$value;
					$path= "cid=$cmd&did=$uniqueId&command=1";
					break;
				case -3: // Down
					$data_json = json_encode( array("name" => "POS_DOWN_CMD" ) );		
					$cmd = -$value;
					$path= "cid=$cmd&did=$uniqueId&command=1";
					break;
				default:
					return;
			}
		}
		else {
			$value = $this->LinearizeToDevice($value);
			$data_json = json_encode( array("name" => "GOTO_POS_CMD","value" => $value ) );		
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

		 $data_json = json_encode( array("name" => "TARGET_TEMPERATURE_CFG","value" => ($value / 10) ) );		
		 $path= "cid=9&did=$uniqueId&goto=$value&command=1";
         break;
	  
      case 'SHUTTERPOS':
      case 'DIMMERPOS':
		 $value = $this->LinearizeToDevice($value);
		 $data_json = json_encode( array("name" => "GOTO_POS_CMD","value" => $value ) );		
		 $path= "cid=9&did=$uniqueId&goto=$value&command=1";
         break;
    }
	
	if( HP_ProtocolVersion($this->GetBridge()) == 5 )
	{
		if( isset($data_json) )
		{
	//		IPS_LogMessage("SymconHP", "Version 5 command $data_json");

			$path="command=$uniqueId=$data_json";
			HP_Request($this->GetBridge(), $path );
			IPS_Sleep(500);

			$this->RequestData();
		}
		return true;
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
		case 12: //  "Troll Comfort"
		case 13: //  "Troll Basis"
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
		case 12: //  "Troll Comfort"
		case 13: //  "Troll Basis"
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
		case 10: //  "Raumthermostat"
		case 14: //  "Heizkörper stellantrieb"
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
		case 14: //  "Heizkörper stellantrieb"
			return $this->SetValue("DESTTEMP", $value);
			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
		case 12: //  "Troll Comfort"
		case 13: //  "Troll Basis"
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
		case 12: //  "Troll Comfort"
		case 13: //  "Troll Basis"
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
		case 12: //  "Troll Comfort"
		case 13: //  "Troll Basis"
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
		case 12: //  "Troll Comfort"
		case 13: //  "Troll Basis"
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
		case 14: //  "Heizkörper stellantrieb"
			return $this->GetValue("DESTTEMP");
			
		case 1: //  "RolloTron"
		case 3: //  "Rohrmotoraktor Umweltsensor"
		case 4: //  "Rohrmotoraktor"
		case 5: //  "Rohrmotor"
		case 6: //  "SX5 Garagentor Stellmotor"
		case 7: //  "Connect-Aktor"
		case 8: //  "RolloTube"
		case 12: //  "Troll Comfort"
		case 13: //  "Troll Basis"
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
		case 12: //  "Troll Comfort"
		case 13: //  "Troll Basis"
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
		case 12: //  "Troll Comfort"
		case 13: //  "Troll Basis"
			return $this->GetValue("AUTOMATIC" );
	}
	return false;
  }



 
}

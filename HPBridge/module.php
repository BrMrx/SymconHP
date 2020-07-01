<?php

class HPBridge extends IPSModule {

  private $Host = "";
  private $HomePilotVersion = 0;
  private $HomePilotCategory = 0;
  private $HomePilotSensorCategory = 0;

  public function Create() {
    parent::Create();
    $this->RegisterPropertyString("Host", "");
    $this->RegisterPropertyInteger("HomePilotVersion", 4);
    $this->RegisterPropertyInteger("HomePilotCategory", 0);
    $this->RegisterPropertyInteger("HomePilotSensorCategory", 0);
    $this->RegisterPropertyInteger("UpdateInterval", 5);
    $this->RegisterPropertyBoolean("HomePilotDebug", false);
  }

  public function ApplyChanges() {
    $this->Host = "";
    $this->Host = "";
    $this->HomePilotVersion = 4;
    $this->HomePilotSensorCategory = 0;

    parent::ApplyChanges();

    $this->RegisterTimer('UPDATE', $this->ReadPropertyInteger('UpdateInterval'), 'HP_SyncStates($id)');

    $this->ValidateConfiguration();
  }

  protected function RegisterTimer($ident, $interval, $script) {
	$id = @IPS_GetObjectIDByIdent($ident, $this->InstanceID);

    if ($id && IPS_GetEvent($id)['EventType'] <> 1) {
      IPS_DeleteEvent($id);
      $id = 0;
    }

    if (!$id) {
      $id = IPS_CreateEvent(1);
      IPS_SetParent($id, $this->InstanceID);
      IPS_SetIdent($id, $ident);
    }

    IPS_SetName($id, $ident);
    IPS_SetHidden($id, true);
    IPS_SetEventScript($id, "\$id = \$_IPS['TARGET'];\n$script;");

    if (!IPS_EventExists($id)) IPS_LogMessage("SymconHP", "Ident with name $ident is used for wrong object type");

    if (!($interval > 0)) {
      IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, 1);
      IPS_SetEventActive($id, false);
    } else {
      IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $interval);
      IPS_SetEventActive($id, true);
    }
  }

  private function ValidateConfiguration() {
    if ($this->ReadPropertyInteger('HomePilotCategory') == 0 ||  
		$this->ReadPropertyInteger('HomePilotSensorCategory') == 0 ||
 		$this->ReadPropertyInteger('HomePilotVersion') == 0 || 
		$this->ReadPropertyString('Host') == '' ) {
      $this->SetStatus(104);
    } else {
      $this->SetStatus(102);
    }
  }


  private function GetHomePilotCategory() {
    if($this->HomePilotCategory == '') {
    	$this->HomePilotCategory = $this->ReadPropertyInteger('HomePilotCategory');
    }	
    return $this->HomePilotCategory;
  }
  
  private function GetHomePilotSensorCategory() {
    if($this->HomePilotSensorCategory == '') {
    	$this->HomePilotSensorCategory = $this->ReadPropertyInteger('HomePilotSensorCategory');
    }	
    return $this->HomePilotSensorCategory;
  }

  private function GetHost() {
    if($this->Host == '') {
    	$this->Host = $this->ReadPropertyString('Host');
    }
    return $this->Host;
  }
  private function GetHomePilotVersion() {
    if($this->HomePilotVersion == '') {
    	$this->HomePilotVersion = $this->ReadPropertyInteger('HomePilotVersion');
    }
    return $this->HomePilotVersion;
  }
  
  public function ProtocolVersion() {
		return $this->GetHomePilotVersion();
  }

  /*
  Direkten Request an den Homepiloten schicken
   Der Parameter $path wird and das Kommando:
   "http://$host/deviceajax.do"
   angehangen
  */
  public function Request( string $path ) {
    $host = $this->GetHost();
	$lProtocolVersion = $this->ProtocolVersion();
 	$lDebug = $this->ReadPropertyBoolean('HomePilotDebug');
 	
 	if( $lDebug )
 	{
		IPS_LogMessage("SymconHP", "Protocol Version ".$this->ProtocolVersion().", request path $path" );
	}
 
	switch( $lProtocolVersion )
	{
		//----------------------- Hompilot Protokollversion 3 und 4 ---------------------------------------
	case 4:
		{
	 
		$client = curl_init();
		curl_setopt($client, CURLOPT_URL, "http://$host/deviceajax.do");
		curl_setopt($client, CURLOPT_USERAGENT, "SymconHP");
		curl_setopt($client, CURLOPT_POSTFIELDS, $path );
		
		curl_setopt($client, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($client, CURLOPT_TIMEOUT, 5);
		curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($client);
		$status = curl_getinfo($client, CURLINFO_HTTP_CODE);
		curl_close($client);

		
		if ($status == '0') {
			$this->SetStatus(203);
			return false;
		} elseif ($status != '200') {
			$this->SetStatus(201);
			return false;
		} else {
			$result = json_decode($result);
		  
			if( $result->status == 'uisuccess' ) {
	//			IPS_LogMessage("SymconHP", "erfolgreich !!");
				return true;
			}
			if( $result->status == 'uierror' ) {
				IPS_LogMessage("SymconHP", "Fehler !!".$result->message );
				return false;
			}

			if ($result->status != 'ok') {
				$this->SetStatus(201);
				return false;
			}

			if( property_exists( $result, "devices" ) ) {
				$result = $result->devices;
			} elseif( property_exists( $result, "meters" ) ) {
				$result = $result->meters;
			} elseif( property_exists( $result, "data" ) ) {
				$result = $result->data;
			} elseif( property_exists( $result, "device" ) ) {
				$result = $result->device;
			}else {
				$result = null;
			}
			$this->SetStatus(102);
			return $result;
		}
		}
		break;
		// ----------- Homepilot Protocolversion 5 ------------------------------------
		case 5: 
		{
			$lSingleNodeDataRequst = false;
			$lParArray = explode('=',  $path );
			
			if( count($lParArray) > 2 )
			{
				if( $lParArray[0] == "command" )
				{
					$lCommandData = $lParArray[2];
					$url = "http://$host/devices/".$lParArray[1];
				}
				else
					$url = "http://$host/v4/devices/".$lParArray[1];
				$lSingleNodeDataRequst = true;
			}
			else
			{
				$url = "http://$host/v4/devices";
				if( $lParArray[0] == "meters" )
					$url .= "?devtype=Sensor";
			}

			$client = curl_init();
			curl_setopt($client, CURLOPT_URL, $url);
			curl_setopt($client, CURLOPT_USERAGENT, "SymconHP");
			if( isset($lCommandData) )
			{
				curl_setopt($client, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json;charset=utf-8',
						'Content-Length: ' . strlen($lCommandData))
					);
				curl_setopt($client, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($client, CURLOPT_POSTFIELDS,$lCommandData);
			
 				if( $lDebug )
 				{
					IPS_LogMessage("SymconHP", "Protocol Version ".$this->ProtocolVersion().", Request $url Command $lCommandData" );
				}
			}
			else
			{
 				if( $lDebug )
 				{
					IPS_LogMessage("SymconHP", "Protocol Version ".$this->ProtocolVersion().", Request $url" );
				}
			}
			
			curl_setopt($client, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($client, CURLOPT_TIMEOUT, 5);
			curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    
			$result = curl_exec($client);
			$status = curl_getinfo($client, CURLINFO_RESPONSE_CODE);
			curl_close($client);

			if ($status != '200') {
				IPS_LogMessage("SymconHP", "Protocol Version ".$this->ProtocolVersion().", Request $url, Error $status" );
				return false;
			} else {
			
        		$result = json_decode($result);
        		
                
				$retVal=null;

				if( $lSingleNodeDataRequst )
				{
					if( property_exists($result,"payload") && 
					    property_exists($result->payload,"device") &&
						property_exists($result->payload->device,"capabilities")	)
					{
						$retVal =$result->payload->device->capabilities;
						if( $lDebug )
 						{
							IPS_LogMessage("SymconHP", "single node request payload:".json_encode($retVal, JSON_PRETTY_PRINT) );
						}
					}
					else
					{
						$retVal = null;
						if( $lDebug )
 						{
							IPS_LogMessage("SymconHP", "single node request - no payload-device-capabilities found!!!" );
							IPS_LogMessage("SymconHP", "Request result: ".json_encode($result, JSON_PRETTY_PRINT) );
						}
					}
						
				}	
				else if( property_exists($result,"devices"))
				{
					$retVal =$result->devices;
					if( $lDebug )
 					{
						IPS_LogMessage("SymconHP", "devices: ".json_encode($retVal, JSON_PRETTY_PRINT) );
					}
				}
				else if( property_exists($result,"device"))
				{
					$retVal[0] = $result->device;
					if( $lDebug )
 					{
						IPS_LogMessage("SymconHP", "device: ".json_encode($retVal, JSON_PRETTY_PRINT) );
					}
				}
				else if( property_exists($result,"meters"))
				{
					$retVal =$result->meters;
					if( $lDebug )
 					{
						IPS_LogMessage("SymconHP", "meters: ".json_encode($retVal, JSON_PRETTY_PRINT) );
					}
				}
				else if( property_exists($result,"transmitters"))
				{
					if( $lDebug )
 					{
						IPS_LogMessage("SymconHP", "transmitters: ".json_encode($retVal, JSON_PRETTY_PRINT) );
					}
					$retVal =$result->transmitters;         
				}
				else if( property_exists($result,"payload")  )
				{
					$retVal =$result->payload;
					if( $lDebug )
 					{
						IPS_LogMessage("SymconHP", "payload: ".json_encode($retVal, JSON_PRETTY_PRINT) );
					}
				}
				else 
				{
					if( $lDebug )
 					{
						IPS_LogMessage("SymconHP", "can not assign result!!!" );
						IPS_LogMessage("SymconHP", "Request result: ".json_encode($result, JSON_PRETTY_PRINT) );
					}	
				}


				$this->SetStatus(102);
				return $retVal;
			}
		}
		break;
	}
	return false;
  }

    /*
   * HP_SyncDevices($bridgeId)
   * Abgleich aller Knoten und Sensoren
   */
  public function SyncDevices() {
	  $this->SyncNodes();
	  $this->SyncSensors();
  }
  /*
   * HP_SyncNodes($bridgeId)
   * Abgleich aller Knoten
   */
  private function SyncNodes() {
    $HomePilotCategoryId = $this->GetHomePilotCategory();
    if(@$HomePilotCategoryId > 0) {
      $nodes = $this->Request('devices=1');
	  
	 	  
      if ($nodes) {
        foreach ($nodes as $nodeId => $node) {


		  $uniqueId = $node->did;
			
          $name = utf8_decode((string)$node->name);
 		  
          $deviceId = $this->GetDeviceByUniqueId($uniqueId);

          if ($deviceId == 0) {
            $deviceId = IPS_CreateInstance($this->NodeGuid());
            IPS_SetProperty($deviceId, 'UniqueId', $uniqueId);
			IPS_LogMessage("HPBridge","Device create new ($name:$uniqueId)");
          }

          IPS_SetParent($deviceId, $HomePilotCategoryId);
          IPS_SetName($deviceId, $name);


          // Verbinde Knoten mit Bridge
          if (IPS_GetInstance($deviceId)['ConnectionID'] <> $this->InstanceID) {
            @IPS_DisconnectInstance($deviceId);
            IPS_ConnectInstance($deviceId, $this->InstanceID);
          }
		  
          IPS_ApplyChanges($deviceId);
		  
		  // Daten zuordnen, Variablen anlegen
		  HP_ApplyJsonData($deviceId, json_encode($node));


        }
      }
	  
    } else {
      echo 'Knoten konnten nicht syncronisiert werden, da die Knotenkategorie nicht zugewiesen wurde.';
      IPS_LogMessage('SymconHP', 'Knoten konnten nicht syncronisiert werden, da die Knotenkategorie nicht zugewiesen wurde.');
    }
    return true;
  }

   /*
   * HP_SyncSensors($bridgeId)
   * Abgleich aller Sensoren
   */
   private function SyncSensors() {
    $HomePilotSensorCategory = $this->GetHomePilotSensorCategory();
    if(@$HomePilotSensorCategory > 0) {
      $sensors = $this->Request('meters=1');
	  
	 	  
      if ($sensors) {
        foreach ($sensors as $sId => $sensor) {

		  $uniqueId = $sensor->did;
			
          $name = utf8_decode((string)$sensor->name);
          $sensorId = $this->GetDeviceByUniqueId($uniqueId);

          if ($sensorId == 0) {
            $sensorId = IPS_CreateInstance($this->SensorGuid());
            IPS_SetProperty($sensorId, 'UniqueId', $uniqueId);
			IPS_LogMessage("HPBridge","Sensor create new ($name:$uniqueId)");
          }

          IPS_SetParent($sensorId, $HomePilotSensorCategory);
          IPS_SetName($sensorId, $name);


          // Verbinde Knoten mit Bridge
          if (IPS_GetInstance($sensorId)['ConnectionID'] <> $this->InstanceID) {
            @IPS_DisconnectInstance($sensorId);
            IPS_ConnectInstance($sensorId, $this->InstanceID);
          }
		  
          IPS_ApplyChanges($sensorId);
		  
		  if( $this->ProtocolVersion() == 4 )
		  {
			$dataRequest = "meter=$uniqueId";
		  
			// nun noch die Daten abfragen
			$data = $this->Request($dataRequest );
		   
			if( $data ) {
			   // Ergänze Daten
			   $sensor->data = $data;
			}
		  }
		  else
		  {
			  $sensor->readings->timestamp = date("d.m.Y H:i:s",$sensor->timestamp);
			  $sensor->data = $sensor->readings;
		  }


		   $lObjInfo = IPS_GetInstance($sensorId);
		   
		   switch( $lObjInfo['ModuleInfo']['ModuleName'] )
		   {
			case 'HPSensor':
				HPSensor_ApplyJsonData($sensorId,json_encode($sensor));
				break;
		   }
        }
      }
	  
    } else {
      echo 'Sensoren konnten nicht syncronisiert werden, da die Sensorkategorie nicht zugewiesen wurde.';
      IPS_LogMessage('SymconHP', 'Sensoren konnten nicht syncronisiert werden, da die Sensorenkategorie nicht zugewiesen wurde.');
    }
    return true;
  }
   
   
  /*
   * HP_SyncStates($bridgeId)
   * Abgleich des Status aller Knoten und Sensoren
   */
  public function SyncStates() {
	$nodes = $this->Request('devices=1');
    if ($nodes) {
      foreach ($nodes as $nodeId => $node) {
        $uniqueId = (string)$node->did;
        $deviceId = $this->GetDeviceByUniqueId($uniqueId);
        if($deviceId > 0) 
			HP_ApplyJsonData($deviceId, json_encode($node));
      }
    }
	$sensors = $this->Request('meters=1');

   if ($sensors) {
      foreach ($sensors as $sId => $sensor) {
        $uniqueId = (string)$sensor->did;
        $sensorId = $this->GetDeviceByUniqueId($uniqueId);
        if($sensorId > 0)
		{
			
		  if( $this->ProtocolVersion() == 4 )
		  {
			$dataRequest = "meter=$uniqueId";
		  
			// nun noch die Daten abfragen
			$data = $this->Request($dataRequest );
		   
			if( $data ) {
			   // Ergänze Daten
			   $sensor->data = $data;
			}
		  }
		  else
		  {
			  $sensor->readings->timestamp = date("d.m.Y H:i:s",$sensor->timestamp);
		      $sensor->data = $sensor->readings;

		  }

			 
		   $lObjInfo = IPS_GetInstance($sensorId);
		   
		   switch( $lObjInfo['ModuleInfo']['ModuleName'] )
		   {
			case 'HPSensor':
				HPSensor_ApplyJsonData($sensorId,json_encode($sensor));
				break;
		   }
		}			
      }
    }
  }

  /*
   * HP_GetDeviceByUniqueId($bridgeId, $uniqueId)
   * Liefert zu einer UniqueID die passende Knoteninstanz
   */
  public function GetDeviceByUniqueId(string $uniqueId) {
    $deviceIds = IPS_GetInstanceListByModuleID($this->NodeGuid());
    foreach($deviceIds as $deviceId) {
      if(IPS_GetProperty($deviceId, 'UniqueId') == $uniqueId) {
        return $deviceId;
      }
    }
	
    $sensorIds = IPS_GetInstanceListByModuleID($this->SensorGuid());
    foreach($sensorIds as $sensorId) {
      if(IPS_GetProperty($sensorId, 'UniqueId') == $uniqueId) {
        return $sensorId;
      }
    }
	return 0;
  }


  private function NodeGuid() {
    return '{F57A1315-EC89-4E96-B883-C17095BC43A7}';
  }

  private function SensorGuid() {
    return '{C6D93407-499E-4C34-B7BF-BD8E89007D83}';
  }
  

}

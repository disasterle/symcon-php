<?

	class Geofancy extends IPSModule
	{
		
		public function Create() {
			//Never delete this line!
			parent::Create();
		}
	
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			$sid = $this->RegisterScript("Hook", "Hook", "<? //Do not delete or modify.\ninclude(IPS_GetKernelDirEx().\"scripts/__ipsmodule.inc.php\");\ninclude(\"../modules/symcon-php/Geofancy/module.php\");\n(new Geofancy(".$this->InstanceID."))->ProcessHookData();");
			$this->RegisterHook("/hook/geofancy", $sid);
		}
		
		private function RegisterHook($Hook, $TargetID)
		{
			$ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
			if(sizeof($ids) > 0) {
				$hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
				$found = false;
				foreach($hooks as $index => $hook) {
					if($hook['Hook'] == "/hook/geofancy") {
						if($hook['TargetID'] == $TargetID)
							return;
						$hooks[$index]['TargetID'] = $TargetID;
						$found = true;
					}
				}
				if(!$found) {
					$hooks[] = Array("Hook" => "/hook/geofancy", "TargetID" => $TargetID);
				}
				IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
				IPS_ApplyChanges($ids[0]);
			}
		}
	
		public function ProcessHookData()
		{
			//workaround for bug
			if(!isset($_IPS))
				global $_IPS;
			if($_IPS['SENDER'] == "Execute") {
				echo "This script cannot be used this way.";
				return;
			}
			if(!isset($_POST['device']) || !isset($_POST['id']) || !isset($_POST['trigger'])) {
				IPS_LogMessage("Geofancy", "Malformed data: " . print_r($_POST, true));
				return;
			}
						
			$deviceID = $this->CreateInstanceByIdent($this->InstanceID, $this->ReduceToIdent($_POST['device']), "Device");
			SetValue($this->CreateVariableByIdent($deviceID, "Latitude", "Latitude", 2), floatval($_POST['latitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Longitude", "Longitude", 2), floatval($_POST['longitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Timestamp", "Timestamp", 1, "~UnixTimestamp"), intval($_POST['timestamp']));
			
			if ($_POST['trigger'] == "test")
			{
				IPS_LogMessage("Geofancy", "Test-Daten: " . print_r($_POST, true));
				return;
			}
			
			SetValue($this->CreateVariableByIdent($deviceID, $this->ReduceToIdent($_POST['id']), utf8_decode($_POST['id']), 0, "~Presence"), $_POST['trigger'] == "enter");
		}
		
		private function ReduceToIdent($text) {
			return preg_replace('/[^a-z0-9]/', '', $text);
		}
		
		private function CreateCategoryByIdent($id, $ident, $name)
		 {
			 $cid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($cid === false)
			 {
				 $cid = IPS_CreateCategory();
				 IPS_SetParent($cid, $id);
				 IPS_SetName($cid, $name);
				 IPS_SetIdent($cid, $ident);
			 }
			 return $cid;
		}
		
		private function CreateVariableByIdent($id, $ident, $name, $type, $profile = "")
		 {
			 $vid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($vid === false)
			 {
				 $vid = IPS_CreateVariable($type);
				 IPS_SetParent($vid, $id);
				 IPS_SetName($vid, $name);
				 IPS_SetIdent($vid, $ident);
				 if($profile != "")
					IPS_SetVariableCustomProfile($vid, $profile);
			 }
			 return $vid;
		}
		
		private function CreateInstanceByIdent($id, $ident, $name, $moduleid = "{485D0419-BE97-4548-AA9C-C083EB82E61E}")
		 {
			 $iid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($iid === false)
			 {
				 $iid = IPS_CreateInstance($moduleid);
				 IPS_SetParent($iid, $id);
				 IPS_SetName($iid, $name);
				 IPS_SetIdent($iid, $ident);
			 }
			 return $iid;
		}
		
	
	}

?>

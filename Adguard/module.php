<?php

declare(strict_types=1);
	class Adguard extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->createVariablenProfiles();

			$this->RegisterPropertyString('IPAddress', '');
        	$this->RegisterPropertyString('User', '');
			$this->RegisterPropertyString('Password', '');
			$this->RegisterPropertyInteger('UpdateTimerInterval', 60);
			

			$this->RegisterVariableBoolean('Reset_Stats', 'Reset_Stats', 'Adguard.Reset', 23);
			$this->EnableAction('Reset_Stats');
			$this->RegisterVariableBoolean('Parental_Filter', 'Parental Filter', '~Switch',20);
			$this->EnableAction('Parental_Filter');
			$this->RegisterVariableBoolean('safesearch', 'safesearch', '~Switch', 21);
			$this->EnableAction('safesearch');
			$this->RegisterVariableBoolean('Filtering', 'Filtering', '~Switch', 21);
			$this->EnableAction('Filtering');
			$this->RegisterVariableInteger('Filterlist_Update_Intervall', 'Filterlist_Update_Intervall', 'Adguard.Filterlist_Update_Intervall', 21);
			$this->EnableAction('Filterlist_Update_Intervall');
			$this->RegisterVariableBoolean('SafeBrowsing', 'SafeBrowsing', '~Switch', 21);
			$this->EnableAction('SafeBrowsing');
			
			//$this->RegisterTimer("Update", 5000, "echo 'Hallo Welt';");
			//$this->SetTimerInterval("Update", 5000);

			$this->RegisterTimer('ADG_updateStatus', 0, 'ADG_updateStatus($_IPS[\'TARGET\']);');

			$this->createVariablenProfiles();
		}
		
		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();


			if ($this->ReadPropertyString('IPAddress') != '') {
				$this->SetTimerInterval('ADG_updateStatus', $this->ReadPropertyInteger('UpdateTimerInterval') * 1000);
			} else {
				$this->SetTimerInterval('ADG_updateStatus', 0);
			}
			
		}
		

		
		private function Curl_Get($User,$Password,$IP,$Get)
		{
			$headers = array(
				'Content-Type:application/json',
				'Authorization: Basic '. base64_encode("$User".':'."$Password") 
							);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$IP"."$Get");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
			return $result = curl_exec($ch);
			//print_r($result);
			if (curl_errno($ch)) 
			{
			echo 'Error:' . curl_error($ch);
			}
			curl_close($ch);

		}

		private function Curl_Post($User,$Password,$IP,$Get)
		{
			$headers = array(
				'Content-Type:application/json',
				'Authorization: Basic '. base64_encode("$User".':'."$Password") 
							);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$IP"."$Get");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
			return $result = curl_exec($ch);
			//print_r($result);
			if (curl_errno($ch)) 
			{
			echo 'Error:' . curl_error($ch);
			}
			curl_close($ch);

		}

		private function Curl_Post_POSTFIELDS_json($User,$Password,$IP,$Get,$Postfield)
		{
			$headers = array(
				'Content-Type:application/json',
				'Authorization: Basic '. base64_encode("$User".':'."$Password") 
							);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$IP"."$Get");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $Postfield);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		
			return $result = curl_exec($ch);
			//print_r($result);
			if (curl_errno($ch)) 
			{
			echo 'Error:' . curl_error($ch);
			}
			curl_close($ch);

		}

		private function Curl_Post_POSTFIELDS($User,$Password,$IP,$Post,$POSTFIELDS)
		{
			$headers = array(
				'Content-Type:text/plain',
				'Authorization: Basic '. base64_encode("$User".':'."$Password"),
				'Accept: */*'
							);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$IP"."$Post");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
		
			return $result = curl_exec($ch);
			//print_r($result);
			if (curl_errno($ch)) 
			{
			echo 'Error:' . curl_error($ch);
			}
			curl_close($ch);

		}

		private function GetStats()
		{
			$result=$this->Curl_Get(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/stats');
			$this->SendDebug(__FUNCTION__ . 'GetStats',$result, 0);
			$json = json_decode($result,true);
        	
			
			$this->RegisterVariableInteger('num_dns_queries', 'num_dns_queries', '', 1);
			$this->SetValue('num_dns_queries', $json["num_dns_queries"]);

			$this->RegisterVariableInteger('num_blocked_filtering', 'num_blocked_filterings', '', 2);
			$this->SetValue('num_blocked_filtering', $json["num_blocked_filtering"]);

			$this->RegisterVariableInteger('num_replaced_safebrowsing', 'num_replaced_safebrowsing', '', 4);
			$this->SetValue('num_replaced_safebrowsing', $json["num_replaced_safebrowsing"]);

			$this->RegisterVariableInteger('num_replaced_safesearch', 'num_replaced_safesearch', '', 5);
			$this->SetValue('num_replaced_safesearch', $json["num_replaced_safesearch"]);

			$this->RegisterVariableInteger('num_replaced_parental', 'nnum_replaced_parental', '', 6);
			$this->SetValue('num_replaced_parental', $json["num_replaced_parental"]);

			$this->RegisterVariableFloat('avg_processing_time', 'avg_processing_time', '', 7);
			$this->SetValue('avg_processing_time', $json["avg_processing_time"]);

		}

		private function ResetStats()
		{
			$result=$this->Curl_Post(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/stats_reset');
			//$this->SendDebug(__FUNCTION__ . 'Recive',$result, 0);
			$json = json_decode($result,true);
			$this->GetStats();

		}

		private function GetParentalFilterStatus()
		{
				$result=$this->Curl_Get(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/parental/status');
				$this->SendDebug(__FUNCTION__ . 'ParentalFilterStatus',$result, 0);
				$json = json_decode($result,true);

			if($json['enabled']==true){
				
				$this->SetValue('Parental_Filter', true);
				$this->SendDebug(__FUNCTION__ . 'ParentalFilterStatus', "JA", 0);
			}
			else 
			{
				$this->SetValue('Parental_Filter', false);
				$this->SendDebug(__FUNCTION__ . 'ParentalFilterStatus', "Nein", 0);
			}
		}

		private function SetParentalFilterStatus(Bool $Value,string $POSTFIELDS)
		{
			If($Value == true)
			{
				$result=$this->Curl_Post_POSTFIELDS(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/parental/enable',$POSTFIELDS);
				//$this->SendDebug(__FUNCTION__ . 'Recive',$result, 0);
				$json = json_decode($result,true);
				$this->GetParentalFilterStatus();
			}
			else
			{
				$result=$this->Curl_Post(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/parental/disable');
				//$this->SendDebug(__FUNCTION__ . 'Recive',$result, 0);
				$json = json_decode($result,true);
				$this->GetParentalFilterStatus();
			}
		}
		
		private function SetSafeSearch(Bool $Value)
		{
			If($Value == true)
			{
				$result=$this->Curl_Post(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/safesearch/enable');
				//$this->SendDebug(__FUNCTION__ . 'Recive',$result, 0);
				$json = json_decode($result,true);
				$this->GetSafeSearchStatus();
			}
			else
			{
				$result=$this->Curl_Post(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/safesearch/disable');
				//$this->SendDebug(__FUNCTION__ . 'Recive',$result, 0);
				$json = json_decode($result,true);
				$this->GetSafeSearchStatus();
			}
		}

		private function GetSafeSearchStatus()
		{
				$result=$this->Curl_Get(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/safesearch/status');
				$this->SendDebug(__FUNCTION__ . 'safesearchStatus',$result, 0);
				$json = json_decode($result,true);

			if($json['enabled']==true){
				
				$this->SetValue('safesearch', true);
				$this->SendDebug(__FUNCTION__ . 'safesearchStatus', "JA", 0);
			}
			else 
			{
				$this->SetValue('safesearch', false);
				$this->SendDebug(__FUNCTION__ . 'safesearchStatus', "Nein", 0);
			}
		}


		private function Setfiltering(bool $Value,int $Intervall)
		{
			If($Value == true)
			{
				$post="{\"enabled\": true,\"interval\": $Intervall}";
				$result=$this->Curl_Post_POSTFIELDS_json(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/filtering/config',$post);	
				$this->GetFiltering();
			}
			else
			{
				$post="{\"enabled\": false,\"interval\": $Intervall}";
				$result=$this->Curl_Post_POSTFIELDS_json(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/filtering/config',$post);
				$this->GetFiltering();
			}
		}

		private function GetFiltering()
		{
				$result=$this->Curl_Get(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/filtering/status');
				//$this->SendDebug(__FUNCTION__ . 'ParentalFilterStatus',$result, 0);
				$json = json_decode($result,true);
				if($json['enabled']==true)
					{
				
					$this->SetValue('Filtering', true);
					//$this->SendDebug(__FUNCTION__ . 'Filtering', "JA", 0);
					}
					else 
					{
					$this->SetValue('Filtering', false);
					//$this->SendDebug(__FUNCTION__ . 'Filtering', "Nein", 0);
					}

				$this->SetValue('Filterlist_Update_Intervall', $json['interval']);
				/*[filters] => Array
					(
						[0] => Array
							(
								[id] => 1
								[enabled] => 1
								[url] => https://adguardteam.github.io/AdGuardSDNSFilter/Filters/filter.txt
								[name] => AdGuard DNS filter
								[rules_count] => 44297
								[last_updated] => 2022-01-15T14:23:11Z
					[whitelist_filters] => 
					[user_rules] => Array
									(
									)		*/	

		}

		private function SetSafeBrowsing(Bool $Value)
		{
			If($Value == true)
			{
				$result=$this->Curl_Post(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/safebrowsing/enable');
				//$this->SendDebug(__FUNCTION__ . 'Recive',$result, 0);
				$json = json_decode($result,true);
				$this->GetSafeBrowsing();
			}
			else
			{
				$result=$this->Curl_Post(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/safebrowsing/disable');
				//$this->SendDebug(__FUNCTION__ . 'Recive',$result, 0);
				$json = json_decode($result,true);
				$this->GetSafeBrowsing();
			}
		}

		private function GetSafeBrowsing()
		{
				$result=$this->Curl_Get(($this->ReadPropertyString('User')),$this->ReadPropertyString('Password'),($this->ReadPropertyString('IPAddress')),'/control/safebrowsing/status');
				$this->SendDebug(__FUNCTION__ . 'SafeBrowsingStatus',$result, 0);
				$json = json_decode($result,true);

			if($json['enabled']==true){
				
				$this->SetValue('SafeBrowsing', true);
			}
			else 
			{
				$this->SetValue('SafeBrowsing', false);
			}
		}




		public function RequestAction($Ident, $Value)
		{
			switch ($Ident) 
			{
				case 'Reset_Stats':
					$this->ResetStats();
					break;
				case 'Parental_Filter':
					$this->SetParentalFilterStatus($Value,"");
					break;
				case 'safesearch':
					$this->SetSafeSearch($Value);
					break;
				case 'Filtering':
					//$this->GetIDForIdent('Filterlist_Update_Intervall');
					$intervall=GetValueInteger($this->GetIDForIdent('Filterlist_Update_Intervall'));
					$this->Setfiltering($Value,$intervall);
					break;
				case 'Filterlist_Update_Intervall':
					//$this->GetIDForIdent('Filterlist_Update_Intervall');
					$State=GetValueBoolean($this->GetIDForIdent('Filtering'));
					$this->Setfiltering($State,$Value);
					break;
				case 'SafeBrowsing':
					$this->SetSafeBrowsing($Value);
					break;
			}
		}




		public function updateStatus()
    {
			$this->GetSafeSearchStatus();
			$this->GetParentalFilterStatus();
			$this->GetStats();
			$this->GetFiltering();
    }
		




		private function createVariablenProfiles()
   		{
        if (!IPS_VariableProfileExists('Adguard.Reset')) 
			{
           		IPS_CreateVariableProfile('Adguard.Reset', 0);
       		}
		   		IPS_SetVariableProfileAssociation("Adguard.Reset", 1, "Reset Stat", "", 0xFFFFFF);
		

		if (!IPS_VariableProfileExists('Adguard.Filterlist_Update_Intervall')) 
		   {
				IPS_CreateVariableProfile('Adguard.Filterlist_Update_Intervall', 1);
			  }
			  	IPS_SetVariableProfileAssociation('Adguard.Filterlist_Update_Intervall', 0, "Disabled", "",-1);
		  		IPS_SetVariableProfileAssociation('Adguard.Filterlist_Update_Intervall', 1, "1h", "",-1);
			  	IPS_SetVariableProfileAssociation('Adguard.Filterlist_Update_Intervall', 12, "12h", "",-1);
				IPS_SetVariableProfileAssociation('Adguard.Filterlist_Update_Intervall', 24, "24h", "",-1);

		}   
		
		
		




	}
	
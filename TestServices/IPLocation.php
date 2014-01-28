<?php

class IPLocation {
	
	public function WEB_SERVICE_INFO() {
		$info = new WSDLInfo ( "IPAddressService" );
		
		$info->addMethod ( "getCountryNameByIp", "getCountryNameByIp", 'ip:string', 'string' );
		
		return $info;
	}
	
	public function getCountryNameByIp($ip) {
		
		$location = json_decode ( file_get_contents ( "http://api.hostip.info/get_json.php?ip=" . $ip ) );
		
		return $location->city . ', ' . $location->country_name . ' (' . $location->country_code . ')';
	}
}
<?php

class TestClass {
	
	public function WEB_SERVICE_INFO() {
		$info = new WSDLInfo ( "TestClass" );
		
		$info->addType ( 'Child', 'id:string,name:string,dob:string' );
		$info->addType ( 'ArrayOfChild', '[Child]' );
		$info->addType ( 'Person', 'id:string,name:string,age:int,children:ArrayOfChild' );
		$info->addType ( 'ArrayOfInt', '[int]' );
		
		$info->addMethod ( "getPerson", "getPerson", 'id:int', 'Person' );
		$info->addMethod ( "addPerson", "addPerson", 'person:Person', 'int' );
		$info->addMethod ( "getPoints", "getPoints", NULL, 'ArrayOfInt' );
		
		return $info;
	}
	
	public function getPoints() {
		return array (1, 3, 4, 5, 6, 7, 8, 9, 10, 11 );
	}
	
	public function getPerson($id) {
		
		$ret = new stdClass ();
		$ret->id = "1";
		$ret->name = "Asim Ishaq";
		$ret->age = 27;
		$ret->children = array ();
		
		for($i = 0; $i < 10; $i ++) {
			$ch = new stdClass ();
			$ch->id = $i;
			$ch->name = "CH-" . $i;
			$ch->dob = "112013";
			$ret->children [] = $ch;
		}
		
		return $ret;
	}
	
	public function addPerson($data) {
		
		return 3;
	}

}

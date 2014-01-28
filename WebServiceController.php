<?php

/**
* Easy PHP SOAP/WSDL Web-Services
* ---------------------------------------------------------------------------------------------
* This is the main controller that will control all the in/out traffic by interpreting URL,
* get and post requests. The goal of this library is to ease the development of standard 
* SOAP/WSDL web-services that can communicate with any client .NET, JAVA, Android etc.  It can 
* perform the following functions:
* ---------------------------------------------------------------------------------------------
* 
* 1) Interpret the URL and extract Service Name and requested Operation. 
* 
* 2) If the URL contains ?WSDL then it will generate WSDL v1.1 document.
*
* 3) If the URL does not contain ?WSDL and it is a GET request then it will display Method 
*    and Type information page.
*
* 4) Handle Soap Request & Response messages (Envelopes). If the arguments are of complex type 
*    ex: an Object or an array of object then it will convert its XML to php object. Similer to 
*    what json_decode does with JSON encoded strings.
*
* 5) Identify the requested operation, convert arguments to php object and then call requested 
*    PHP function with these arguments. Finally convert the return value to XML format according 
*    to the schema information, prepare XML SOAP Envelop message and send it to client.
*
*
* Example Web-Service: (using basic types)
* ----------------------------------------------------------------------------------------------
* # Name: 			MathService
* # Description:	Perform basic add & subtract operations on two numbers 	
*
* class MathService {
* 
* 	public function add($a, $b) { return $a+$b; }
* 	public function subtract($a, $b) { return $a-$b; }
* 
* 	//The developer must include this public method in order to expose its functions in web-service
* 	public function WEB_SERVICE_INFO () {
* 		
* 		$sInfo = new WSDLInfo ("MathService");
* 		$sInfo->addMethod ("add","add","a:float,b:float","float");
* 		$sInfo->addMethod ("subtract","subtract","a:float,b:float","float");
* 		
* 		return $sInfo;	
* 	}
* 
* }
* 
* ------------------------------------------------------------------------------------------------
* For detail usage information and examples (which includes complex data types) visit:
* # http://asimishaq.com/resources/easy-soap-web-service-php
* ------------------------------------------------------------------------------------------------
* 
* Author:
* --------
* Name		Asim Ishaq
* Email 	asim709@gmail.com
* Web	 	http://www.asimishaq.com
*
* Copyright (c) 2013 Asim Ishaq
*
* License: GPL v2 or later
* 
*/

error_reporting ( E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING ^ E_ERROR ^ E_STRICT );

require_once ('WSDLInfo.php');

// =======================================================================//
// EXTRACT WEB SERVICE PATH, CLASS NAME FROM URL
// =======================================================================//

/**
 * Important Variables name and meaning:
 *
 * # $base_url : The url for the current directory i.e; in which
 * WebServiceController is present
 * # $request_url : Full request url including protocol
 * # $resource_url : Url of request exluding base URL and GET parameters
 * # $resource_path : Relative path of PHP class for this web service
 * # $class_name : Name of the web service class
 * # $service_url : URL of the web service excluding Get parameters
 */

$base_url = dirname ( isset ( $_SERVER ['HTTPS'] ) ? 'https://' : 'http://' . $_SERVER ['SERVER_NAME'] . $_SERVER ['SCRIPT_NAME'] );

$request_url = isset ( $_SERVER ['HTTPS'] ) ? 'https://' : 'http://' . $_SERVER ['SERVER_NAME'] . $_SERVER ['REQUEST_URI'];
$resource_url = str_replace ( $base_url, "", $request_url );
if (substr ( $resource_url, 0, 1 ) == '/') {
	$resource_url = substr ( $resource_url, 1, strlen ( $resource_url ) );
}
$arr = explode ( '?', $resource_url );
$resource_url = $arr [0];

$resource_path = $resource_url . '.php';

if (! file_exists ( $resource_path )) {
	die ( 'The requested resource does not exist' );
}

require_once $resource_path;

$arr = explode ( '/', $resource_url );
$class_name = $arr [sizeof ( $arr ) - 1];

if (! class_exists ( $class_name )) {
	die ( 'The resource class does not exist' );
}

$arr = explode ( '?', $request_url );
$service_url = $arr [0];

// =======================================================================//
// INSTANTIATE WEB SERVICE CLASS
// =======================================================================//

$rClass = new ReflectionClass ( $class_name );
$class = $rClass->newInstance ();

// WSDL INFO
$wsdlInfo = $class->WEB_SERVICE_INFO ();

if (isset ( $_GET ['wsdl'] ) || isset ( $_GET ['WSDL'] )) {
	
	// =======================================================================//
	// SEND WSDL DOCUMENT
	// =======================================================================//
	
	header ( 'content-type:text/xml' );
	echo $wsdlInfo->getWsdl ( $service_url );

} else if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
	
	// =======================================================================//
	// HANDLE SOAP METHOD CALL / PARSE SOAP ENVELOP MESSAGE
	// =======================================================================//
	
	header ( 'content-type:text/xml' );
	
	$xmlData = file_get_contents ( "php://input" );
	$xmlData = str_replace ( chr ( 13 ), '', $xmlData );
	
	// Remove Soap Envelop, header and closing tags of evelope and header
	$methodXml = preg_replace ( '/<[^\/\n]*Envelope.*<[^\/]*Body>/is', '', $xmlData );
	$methodXml = preg_replace ( '/<\/[a-zA-Z_\-\:]*Body>/i', '', $methodXml );
	$methodXml = preg_replace ( '/<\/[a-zA-Z_\-\:]*Envelope>/i', '', $methodXml );
	
	// Get Method Name
	preg_match ( '/\<[0-9a-zA-Z\:\-\_]+/i', $methodXml, $matches );
	$methodName = trim ( preg_replace ( '/\</i', '', $matches [0] ) );
	$methodName = trim ( preg_replace ( '/.*\:/i', '', $methodName ) );
	
	// Convert xml to object
	$xmlObj = json_decode ( json_encode ( ( array ) simplexml_load_string ( $methodXml ) ) );
	
	// Normalize the xmlObject -- convert the key items to array if it is object
	normalizeReqXmlObj ( $xmlObj );
	
	// get method info
	$methodInfo = $wsdlInfo->getMethodByName ( $methodName );
	
	// Prepares func params array
	$params = array ();
	foreach ( $xmlObj as $key => $value ) {
		array_push ( $params, $value );
	}
	
	$ret = call_user_func_array ( array ($class, $methodInfo ['func_name'] ), $params );
	
	// Convert ret obj to xml if it is a complex datatype
	if ($wsdlInfo->getTypeByName ( $methodInfo ['return_type'] ) !== FALSE) {
		
		// convert ret object to xml
		$xmlObj = false;
		convertObjToXml ( $ret, $xmlObj );
		
		// Remove XM Version Tag and Root Tag
		$ret = $xmlObj->asXML ();
		$ret = preg_replace ( '/<\?xml.*\?>\n*/i', '', $ret );
		$ret = preg_replace ( '/<\/*root>\n*/i', '', $ret );
	}
	
	// =======================================================================//
	// SEND SOAP ENVELOP RESPONSE
	// =======================================================================//
	
	$response = '<?xml version="1.0" encoding="utf-8"?>';
	$response .= '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:ns="' . $service_url . '">';
	$response .= '<soap:Body>';
	$response .= '<ns:' . $methodName . 'Response>';
	$response .= '<ns:result xmlns="' . $service_url . '">' . $ret . '</ns:result>';
	$response .= '</ns:' . $methodName . 'Response>';
	$response .= '</soap:Body>';
	$response .= '</soap:Envelope>';
	
	echo str_replace ( chr ( 13 ), '', $response );

} else {
	
	// =======================================================================//
	// PRINT WEB SERIVE METHOD INFORMATION
	// =======================================================================//
	
	echo '<!doctype html>
		  <html>
		  <head>
		  	<title>' . $wsdlInfo->getServiceName () . ' (WSDL/SOAP Web-Service v1.2)</title>
		  	<style type="text/css">
		  		html,body { font-family:arial; font-size:13px;margin:0px;padding:0px;}
		  		h1,h2,h3,h4 {font-family:tahoma;margin:0px;margin-top:20px;margin-bottom:20px;}
		  		h1 {font-size:20px;border-top:7px solid orange;padding:5px;font-weight:normal}
		  		h3 {font-size:15px;}
		  		a {color:purple; text-decoration:none;}
		  		table {background-image: -webkit-linear-gradient(top, white 0%, #F2F2F2 100%);border: 1px solid #ccc;border-bottom-left-radius: 3px;	border-bottom-right-radius: 3px;border-top-left-radius: 3px;border-top-right-radius: 3px;}
		  		table thead tr th {color:#333;border-bottom:1px dotted #aaa;text-align:left}
		  		hr {display: block;-webkit-margin-before: 0.5em;-webkit-margin-after: 0.5em;-webkit-margin-start: auto;-webkit-margin-end: auto;border-style: inset;border-width: 1px;}
		  		.wrap {padding-left:10px;}
		  	</style>
		  </head>
		  <body>';
	
	echo '<h1 style="margin-top:0px;padding-bottom:15px">WebService: ' . $wsdlInfo->getServiceName () . ' &nbsp;<hr/>
		  <a href="' . $service_url . '?wsdl" target="_blank" style="font-size:16px">' . $service_url . '?wsdl</a></h1>';
	
	$methods = $wsdlInfo->getMethods ();
	
	echo '<div class="wrap">';
	echo '<h3>Available Methods&nbsp;(' . sizeof ( $methods ) . ')</h3>';
	
	echo '<table border="0px" cellpadding="10px" cellspacing="0px">
		  	<thead>
		  		<tr>
		  			<th>Name</th><th>Arguments</th><th>Return Type</th>
		  		</tr>
		  	</thead>
		  	<tbody>
	';
	
	foreach ( $methods as $method ) {
		echo '<tr>';
		// Method Name
		echo '<td valign="top">' . $method ['web_method'] . '</td>';
		
		// Arguments
		echo '<td>';
		foreach ( $method ['parameters'] as $parameter ) {
			echo $parameter [0] . ' (' . $parameter [1] . ')<br/>';
		}
		echo '</td>';
		
		// Return Type
		echo '<td valign="top">' . $method ['return_type'] . '</td>';
		
		echo '</tr>';
	}
	echo '</tbody>
		</table>';
	
	// =======================================================================//
	// PRINT WEB SERIVE TYPE INFORMATION
	// =======================================================================//
	
	$types = $wsdlInfo->getTypes ();
	
	if (sizeof ( $types ) <= 0)
		exit ();
	
	echo '<h3>Custom Data Types&nbsp;(' . sizeof ( $types ) . ')</h3>';
	
	echo '<table border="0px" cellpadding="10px" cellspacing="0px">
		  <thead>
			<tr>
				<th>Name</th><th>Definition</th>
			</tr>
		  </thead>
		  <tbody>';
	
	foreach ( $types as $type ) {
		
		// type
		echo '<tr><td valign="top">' . $type ['name'] . '</td>';
		
		// def
		echo '<td>';
		foreach ( $type ['vars'] as $var ) {
			echo $var ['name'] . ' (' . ($var ['is_array'] == true ? '<i>Array Of</i> ' : '') . $var ['type'] . ')<br/>';
		}
		
		echo '</td></tr>';
	}
	
	echo '</tbody>
		  </table>
		  </div>
		  </body>';
}

exit ();

// ====================================================================//
// UTILITY FUNCTIONS
// ====================================================================//

function get_mime_type($extension) {
	$mime_types = array ("pdf" => "application/pdf", "exe" => "application/octet-stream", "zip" => "application/zip", "docx" => "application/msword", "doc" => "application/msword", "xls" => "application/vnd.ms-excel", "ppt" => "application/vnd.ms-powerpoint", "gif" => "image/gif", "png" => "image/png", "jpeg" => "image/jpg", "jpg" => "image/jpg", "mp3" => "audio/mpeg", "wav" => "audio/x-wav", "mpeg" => "video/mpeg", "mpg" => "video/mpeg", "mpe" => "video/mpeg", "mov" => "video/quicktime", "avi" => "video/x-msvideo", "3gp" => "video/3gpp", "css" => "text/css", "jsc" => "application/javascript", "js" => "application/javascript", "php" => "text/html", "htm" => "text/html", "html" => "text/html", "swf" => "application/x-shockwave-flash", "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" );
	
	return $mime_types [$extension];
}

// If there is a key named items than that key must contain an array if it
// contains an object then enclose that object in Array
function normalizeReqXmlObj(&$obj) {
	
	foreach ( $obj as $key => &$value ) {
		// Assumption: The items key must contain an array
		if ($key == 'items') {
			
			if (! is_array ( $value )) {
				$val = clone $value;
				$value = array ();
				array_push ( $value, $val );
			
			}
		} else if (is_object ( $value )) {
			
			normalizeReqXmlObj ( $value );
		}
	}
}

function convertObjToXml($obj, &$xmlObj) {
	
	if ($xmlObj === false)
		$xmlObj = new SimpleXMLElement ( "<root/>" );
	
	if (is_object ( $obj )) {
		foreach ( $obj as $key => &$value ) {
			
			if (is_object ( $value ) || is_array ( $value )) {
				$c = $xmlObj->addChild ( $key );
				convertObjToXml ( $value, $c );
			} else {
				$xmlObj->addChild ( $key, $value );
			}
		}
	} else if (is_array ( $obj )) {
		
		foreach ( $obj as $k => &$v ) {
			
			if (is_object ( $v ) || is_array ( $v )) {
				$c = $xmlObj->addChild ( "items" );
				convertObjToXml ( $v, $c );
			} else {
				$xmlObj->addChild ( "items", $v );
			}
		}
	
	}
}

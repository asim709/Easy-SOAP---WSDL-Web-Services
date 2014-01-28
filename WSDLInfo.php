<?php

/**
* WSDLInfo class - Easy PHP SOAP/WSDL Web-Services
* -----------------------------------------------------------------------------------------
* This class is used to define methods (operations) and data types to prepare WSDL document.
* It also supports complex/custom data types including array of objects as well. While creating 
* webservice the developer must create a public function named WEB_SERVICE_INFO that return an
* instance of WSDLInfo class. By this function the WebServiceController will identify the method
* names, their paramteres and return type information. 
*
* It also generates WSDL document according to the v1.1 Specs that is most widely used.
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
*/

class WSDLInfo {
	
	private $_methods;
	private $_serviceName;
	private $_types;
	
	public function __construct($serviceName) {
		$this->_methods = array ();
		$this->_types = array ();
		$this->_serviceName = $serviceName;
	}
	
	public function setServiceName($serviceName) {
		$this->_serviceName = $serviceName;
	}
	
	public function getServiceName() {
		return $this->_serviceName;
	}
	
	public function addType($name, $definition) {
		
		$vars = array ();
		$items = explode ( ',', $definition );
		foreach ( $items as $item ) {
			// Check if it is an array
			if (preg_match ( '/\[.+\]/', $item ) == 1) {
				$type = preg_replace ( '/\[|\]/', '', $item );
				array_push ( $vars, array ("name" => "items", "type" => $type, "is_array" => true ) );
			} else {
				// Else name and type pair
				$pair = explode ( ':', $item );
				array_push ( $vars, array ("name" => $pair [0], "type" => $pair [1], "is_array" => false ) );
			}
		}
		
		array_push ( $this->_types, array ("name" => $name, "vars" => $vars ) );
	}
	
	public function getTypeByName($name) {
		foreach ( $this->_types as $type ) {
			if ($type ['name'] == $name)
				return $type;
		}
		return false;
	}
	
	public function getTypes() {
		return $this->_types;
	}
	
	public function getNSByType($type) {
		// xs is used for basic types where as ns is used for cutsom types
		foreach ( $this->_types as $typ ) {
			if ($typ ['name'] == $type)
				return 'ns';
		}
		return 'xs';
	}
	
	public function addMethod($webMethod, $functionName, $parameters = NULL, $returnType = NULL) {
		
		$arr = array ();
		
		if ($parameters != NULL) {
			$params = explode ( ',', $parameters );
			foreach ( $params as $param ) {
				$items = explode ( ':', $param );
				array_push ( $arr, array ($items [0], $items [1] ) );
			}
		}
		
		array_push ( $this->_methods, array ("web_method" => $webMethod, "func_name" => $functionName, "parameters" => $arr, "return_type" => $returnType ) );
	}
	
	public function getMethods() {
		return $this->_methods;
	}
	
	public function getMethodByName($webMethod) {
		foreach ( $this->_methods as $method ) {
			if ($method ['web_method'] == $webMethod)
				return $method;
		}
		return false;
	}
	
	/**
	 * Generate the contents of WSDL file in XML format according to WSDL v1.2
	 * Standard
	 */
	public function getWsdl($service_url) {
		
		$definitions = '<?xml version="1.0" encoding="utf-8"?>
<definitions name="' . $this->getServiceName () . '"  
						xmlns="http://schemas.xmlsoap.org/wsdl/"
						xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
						xmlns:http="http://schemas.xmlsoap.org/wsdl/http/" 
						xmlns:xs="http://www.w3.org/2001/XMLSchema" 
						xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
						xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/"
						xmlns:ns="' . $service_url . '"
						targetNamespace="' . $service_url . '"
		>';
		
		// =======================================================================//
		// ========= TYPES
		// =======================================================================//
		
		$type = '<types>
				 <xs:schema elementFormDefault="qualified" targetNamespace="' . $service_url . '">';
		
		// #Define custom/complex types
		foreach ( $this->_types as $typ ) {
			$type .= '<xs:complexType name="' . $typ ['name'] . '">
					  <xs:sequence>';
			
			$vars = $typ ['vars'];
			foreach ( $vars as $var ) {
				
				$ns = $this->getNSByType ( $var ['type'] );
				$type .= '<xs:element name="' . $var ['name'] . '" type="' . $ns . ':' . $var ['type'] . '" ';
				
				if ($var ['is_array'] == true) {
					$type .= 'minOccurs="0" maxOccurs="unbounded" ';
				}
				
				$type .= '/>';
			}
			
			$type .= '</xs:sequence>
					  </xs:complexType>';
		}
		
		// # Define Request & Repsponse Parameters and Return types
		foreach ( $this->_methods as $method ) {
			
			$type .= '<xs:element name="' . $method ['web_method'] . '">';
			
			$type .= '<xs:complexType>
						<xs:sequence>';
			
			foreach ( $method ['parameters'] as $parameter ) {
				$ns = $this->getNSByType ( $parameter [1] );
				$type .= '<xs:element name="' . $parameter [0] . '" type="' . $ns . ':' . $parameter [1] . '"/>';
			}
			
			$type .= '</xs:sequence>
					</xs:complexType>';
			
			$type .= '</xs:element>';
			
			if ($method ['return_type'] != NULL) {
				$type .= '<xs:element name="' . $method ['web_method'] . 'Response">';
				$type .= '<xs:complexType>
							<xs:sequence>';
				
				$ns = $this->getNSByType ( $method ['return_type'] );
				$type .= '<xs:element name="result" type="' . $ns . ':' . $method ['return_type'] . '"/>';
				
				$type .= '</xs:sequence>
							</xs:complexType>';
				$type .= '</xs:element>';
			}
		}
		
		$type .= '</xs:schema>
				  </types>';
		
		// =======================================================================//
		// ========= MESSAGES
		// =======================================================================//
		
		$message = '';
		// # Reuest and Response Messages
		foreach ( $this->_methods as $method ) {
			
			// # Input Message
			$message .= '<message name="' . $method ['web_method'] . 'Message">';
			$message .= '<part name="parameters" element="ns:' . $method ['web_method'] . '"/>';
			$message .= '</message>';
			
			// # Output Message
			$message .= '<message name="' . $method ['web_method'] . 'ResponseMessage">';
			if ($method ['return_type'] != NULL) {
				$message .= '<part name="parameters" element="ns:' . $method ['web_method'] . 'Response"/>';
			}
			$message .= '</message>';
		}
		
		// =======================================================================//
		// ========= PORT TYPE / INTERFACE
		// =======================================================================//
		
		$portType = '<portType name="' . $this->getServiceName () . 'Interface">';
		
		foreach ( $this->_methods as $method ) {
			
			$portType .= '<operation name="' . $method ['web_method'] . '">';
			
			// # Input Message
			$portType .= '<input message="ns:' . $method ['web_method'] . 'Message"/>';
			
			// # Output Message
			if ($method ['return_type'] != NULL) {
				$portType .= '<output message="ns:' . $method ['web_method'] . 'ResponseMessage"/>';
			}
			
			$portType .= '</operation>';
		}
		
		$portType .= '</portType>';
		
		// =======================================================================//
		// ========= BINDING
		// =======================================================================//
		
		$binding = '<binding name="' . $this->getServiceName () . 'SoapHttpBinding" type="ns:' . $this->getServiceName () . 'Interface">
					<soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>';
		
		foreach ( $this->_methods as $method ) {
			
			$binding .= '<operation name="' . $method ['web_method'] . '">
						 <soap:operation style="document"/>';
			
			// # Input
			$binding .= '<input><soap:body use="literal"/></input>';
			
			// # Output
			if ($method ['return_type'] != NULL) {
				$binding .= '<output><soap:body use="literal"/></output>';
			}
			
			$binding .= '</operation>';
		}
		
		$binding .= '</binding>';
		
		// =======================================================================//
		// ========= SERVICE
		// =======================================================================//
		
		$service = '<service name="' . $this->getServiceName () . 'Service">';
		$service .= '<port name="' . $this->getServiceName () . 'Endpoint" binding="ns:' . $this->getServiceName () . 'SoapHttpBinding">';
		$service .= '<soap:address location="' . $service_url . '"/>';
		$service .= '</port>';
		$service .= '</service>';
		
		return $definitions . $type . $message . $portType . $binding . $service . '</definitions>';
	}
}

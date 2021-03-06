<?php

//ini_set( "display_errors", "On" );

// directories
define( "ROUTE_ROOT_DIR", dirname( $_SERVER["PHP_SELF"] ) . "/" );
define( "ROUTE_INCLUDE_DIR", "./include/" );
define( "ROUTE_MODEL_DIR", "./model/" );
define( "ROUTE_CONTROLLER_DIR", "./controller/" );
define( "ROUTE_VIEW_DIR", "./view/" );

// errors
define( "ROUTE_ERROR_INVALID_PARAMS", "invalid parameter" );
define( "ROUTE_ERROR_NOTFOUND", "not found" );

class Route {
	
	private $route = "";
	private $class = "home";
	private $method = "index";
	private $args = array();

	private $routeChars = "/^[a-zA-Z0-9\-\.\/_]*$/";
	private $routeDelimiter = "/";

	public function Route(){
		// include resources
		if( is_dir( ROUTE_INCLUDE_DIR ) ){
			$d = opendir( ROUTE_INCLUDE_DIR );
			while( ($file = readdir($d)) !== false ){
				if( preg_match( "/^[^\._].+(\.php)$/", $file ) ){
					require_once( ROUTE_INCLUDE_DIR . $file );
				}
			}
		}
		// initialize route
		$this->init();
		// run
		$this->run();
	}

	private function init(){
		if( isset( $_GET["route"] ) ){
			$this->route = preg_replace( "/\/$/", "", $_GET["route"] );
		}
		if( !preg_match( $this->routeChars, $this->route ) ){
			throw new Exception( ROUTE_ERROR_INVALID_PARAMS );
		}
		$r = explode( $this->routeDelimiter, $this->route );
		$c = array_shift( $r );
		$m = array_shift( $r );
		$this->class = ($c) ? $c : $this->class;
		$this->method = ($m) ? $m : $this->method;
		$this->args = ( count($r) === 1 ) ? $r[0] : $r;
	}

	private function run(){
		$classfile = ROUTE_CONTROLLER_DIR . "{$this->class}.php";
		if( !file_exists( $classfile ) ){
			throw new Exception( ROUTE_ERROR_NOTFOUND );
		}
		if( preg_match( "/^_/", $this->method ) ){
			throw new Exception( ROUTE_ERROR_NOTFOUND );
		}
		require_once( $classfile );
		if( !method_exists( $this->class, $this->method ) ){
			throw new Exception( ROUTE_ERROR_NOTFOUND );
		}
		$inst = new $this->class;
		$inst->{$this->method}( $this->args );
	}
}

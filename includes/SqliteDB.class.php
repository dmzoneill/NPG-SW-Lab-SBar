<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

class SqliteDB extends DB
{
  private $db = null;

  public function __construct()
  {
	  $this->db = "barcode.db";
    $this->connect();	
  }
  
  public function connect()
  {
    try
    {
      if( file_exists( $this->db ) == false )
      {
        touch( $this->db );
      }
      
      $this->conn = new PDO( 'sqlite:' . $this->db );
	    $this->conn->query( "PRAGMA synchronous = OFF" );
    }
    catch( Exception $e )
    { 
      $this->conn = null; 
    }
  }  
}


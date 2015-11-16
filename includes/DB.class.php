<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

class DB implements Idbo
{
  protected $conn = null;
    
  public function disconnect()
  {
    try
    {
      unset( $this->conn );
      $this->conn = null;
    }
    catch( Exception $e )
    { 
      $this->conn = null; 
    }
  }
  
  public function is_connected()
  {
    return $this->conn == null ? false : true;
  }
  
  public function query( $sql )
  {
    $result = $this->conn->query( $sql );	
	  return $result;
  }  
  
  public function connect()
  {
    return false;
  } 
  
  public function get_error()
  {
    return $this->conn->errorInfo();
  }
  
  public function __get( $property )
  {
    if(property_exists( $this , $property ) ) 
    {    
      return $this->{$property};
    }
  }
}

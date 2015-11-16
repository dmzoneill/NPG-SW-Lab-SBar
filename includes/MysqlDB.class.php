<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

class MysqlDB extends DB
{
  public function __construct()
  {
    $this->connect();	
  }
  
  public function connect()
  {
    try
    {
      $this->conn = new PDO( 'mysql:host=' . __DBHOST__ . ';dbname=' . __DBNAME__ . '', __DBUSER__ , __DBPASS__ );
    }
    catch( Exception $e )
    { 
      $this->conn = null; 
    }
  }  
}


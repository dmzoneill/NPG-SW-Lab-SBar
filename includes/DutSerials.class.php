<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

class DutSerials extends BarcodeGenerator
{  
  public function __construct()
  {
    parent::__construct();	
    $this->tablename = __TABLE_PREFIX__ . "_dut"; 
    
    if( $this->dbo->is_connected() == true )
    {
      $this->create_tables();
    }
  }
  
  protected function create_tables()
  {
    parent::create_tables();
    
    if( defined( '__DBTYPE__' ) == false || __DBTYPE__ == "mysql" )
    {    
      $createtable = "CREATE TABLE IF NOT EXISTS `". $this->tablename . "` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `barcode` varchar(8) NOT NULL,
                    `creator` int(11) NOT NULL,
                    `created` varchar(15) NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `barcode` (`barcode`),
                    UNIQUE KEY `id` (`id`),
                    KEY `creator` (`creator`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
                    
      $result = $this->dbo->query( $createtable );	
      
      if( $result == false )
      {
        die( 'Unable to create table ' . $this->tablename );
      }
      
      $constraintable = "ALTER TABLE `". $this->tablename . "`
                      ADD CONSTRAINT `" . __TABLE_PREFIX__ . "_dut_ibfk_1` FOREIGN KEY (`creator`) REFERENCES `" . __TABLE_PREFIX__ . "_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION";
                      
      $this->dbo->query( $constraintable );	    
    }
    else
    {
      //sqlite
      $createtable = "CREATE TABLE IF NOT EXISTS `". $this->tablename . "` (
                    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                    `barcode` varchar(8) NOT NULL UNIQUE,
                    `creator` INTEGER NOT NULL,
                    `created` varchar(15) NOT NULL,
                    UNIQUE(id, barcode),
                    FOREIGN KEY (id) REFERENCES user(id));";
                    
      $result = $this->dbo->query( $createtable );	
    
      if( $result == false )
      {
        die( 'Unable to create table ' . $this->tablename );
      }  
    }                     
  }  
  
  protected function checkout_barcode( $next )
  {
    $insert = "INSERT INTO ". $this->tablename . " VALUES( NULL,'" . $next . "','" . $this->userid . "','" . time() . "');";
    $this->dbo->query( $insert );  
  }
  
  protected function get_next_barcode( $specno = false )
  {
    $last = $this->get_last_barcode();	
    
    if( $last == false )
    {
      $this->checkout_barcode( sprintf( "%s%05d" , "A", 1 ) );
      return sprintf( "%s%05d" , "A", 1 );
    }
    
    $letter = $last[0];
    $num = substr( $last , 1 );
    $nextnum = $num + 1;
 
    if( $nextnum > 99999 )
    {
      $nextnum = 1;
      $letter = $this->next_letter( $letter );
      
      if( $letter == false )
      {
        return "Exhausted letter prefix";
      }		  
    }	
 
    $this->checkout_barcode( sprintf( "%s%05d" , $letter, $nextnum ) );
		
    return sprintf( "%s%05d" , $letter, $nextnum );
  }
}
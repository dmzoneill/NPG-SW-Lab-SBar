<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

class Hvpip extends BarcodeGenerator
{  
  public function __construct()
  {
    parent::__construct();	
    $this->tablename = __TABLE_PREFIX__ . "_hvpip";
    
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
                      `barcode` varchar(6) NOT NULL,
                      `specno` int(11) NOT NULL,
                      `creator` int(11) NOT NULL,
                      `created` varchar(15) NOT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `barcode` (`barcode`),
                      UNIQUE KEY `id` (`id`),
                      KEY `creator` (`creator`),
                      KEY `specno` (`specno`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
      
      $result = $this->dbo->query( $createtable ); 
      
      if( $result == false )
      {
        print_r( $this->dbo->get_error() );
        die( 'Unable to create table barcode_' . $this->tablename );
      } 
      
      $createtable = "CREATE TABLE IF NOT EXISTS `" . __TABLE_PREFIX__ . "_specno` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `specno` varchar(25) NOT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `specno` (`specno`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
      
      $this->dbo->query( $createtable );	
      
      $result = $this->dbo->query( $createtable ); 
      
      if( $result == false )
      {
        print_r( $this->dbo->get_error() );
        die( 'Unable to create table barcode_' . __TABLE_PREFIX__ . '_specno' );
      } 
      
      $constraintable = "ALTER TABLE `". $this->tablename . "`
                        ADD CONSTRAINT `" . __TABLE_PREFIX__ . "_hvpip_ibfk_1` FOREIGN KEY (`creator`) REFERENCES `" . __TABLE_PREFIX__ . "_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                        ADD CONSTRAINT `" . __TABLE_PREFIX__ . "_hvpip_ibfk_2` FOREIGN KEY (`specno`) REFERENCES `" . __TABLE_PREFIX__ . "_specno` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;";
                            
      $result = $this->dbo->query( $createtable ); 
      
      if( $result == false )
      {
        print_r( $this->dbo->get_error() );
        die( 'Unable to add table constraints' );
      }  
    }
    else
    {
      $createtable = "CREATE TABLE IF NOT EXISTS `" . __TABLE_PREFIX__ . "_specno` (
                      `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                      `specno` varchar(25) NOT NULL UNIQUE
                    );";
      
      $result = $this->dbo->query( $createtable ); 
      
      if( $result == false )
      {
        print_r( $this->dbo->get_error() );
        die( 'Unable to create table barcode_' . __TABLE_PREFIX__ . '_specno' );
      }  
      
      $createtable = "CREATE TABLE IF NOT EXISTS `". $this->tablename . "` (
                      `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                      `barcode` varchar(6) NOT NULL UNIQUE,
                      `specno` INTEGER NOT NULL,
                      `creator` INTEGER NOT NULL,
                      `created` varchar(15) NOT NULL,
                      UNIQUE(id, barcode, specno),
                      FOREIGN KEY (creator) REFERENCES " . __TABLE_PREFIX__ . "_user(id),
                      FOREIGN KEY (specno) REFERENCES " . __TABLE_PREFIX__ . "_specno(id)
                    );";
      
      $result = $this->dbo->query( $createtable ); 
      
      if( $result == false )
      {
        print_r( $this->dbo->get_error() );
        die( 'Unable to create table barcode_' . $this->tablename );
      }      
    }
  }  
  
  protected function get_last_specno()
  {
    $last = "SELECT specno FROM `" . __TABLE_PREFIX__ . "_specno` ORDER BY id DESC LIMIT 1;";
    $ps = $this->dbo->query( $last );
    $ps = $ps->fetchColumn();	    
    return $ps;  
  }
  
  protected function create_specno( $specno )
  {
    $getspecno = "SELECT id FROM `" . __TABLE_PREFIX__ . "_specno` WHERE specno= '" . $specno . "'";
    $result = $this->dbo->query( $getspecno );
        
    if( $result->rowCount() == 0 )
    {
      $insertspecno = "INSERT INTO `" . __TABLE_PREFIX__ . "_specno` VALUES( NULL, '" . $specno . "');";
      $this->dbo->query( $insertspecno );
      
      $getspecno = "SELECT id FROM `" . __TABLE_PREFIX__ . "_specno` WHERE specno= '" . $specno . "'";
      $result = $this->dbo->query( $getspecno );
      return $result->fetchColumn();
    }
    
    return false;
  }

  protected function checkout_barcode( $next , $specno )
  {
    $insertspecno = "INSERT INTO `" . __TABLE_PREFIX__ . "_specno` VALUES( NULL, '" . $specno . "');";
    $this->dbo->query( $insertspecno );
    
    $getspecno = "SELECT id FROM `" . __TABLE_PREFIX__ . "_specno` WHERE specno= '" . $specno . "'";
    $result = $this->dbo->query( $getspecno ); 
    $id = $result->fetchColumn(); 
    
    $insert = "INSERT INTO ". $this->tablename . " VALUES( NULL, '" . $next . "','" . $id . "','" . $this->userid . "','" . time() . "');";
    $this->dbo->query( $insert );
  }

  protected function get_next_barcode( $specno = false )
  {
    $insspec = $specno;

    if( $specno == false )
    {
      $insspec = $this->get_last_specno();
      
      if( $insspec == false )
      {
        $this->error = true;
        // no spec number in the db
        // user should provide spec code to get barcode
        return "No spec number available, trying creating one?";
      }
    }
    
    $createdSpecNo = ( $specno == false ) ? false : $this->create_specno( $specno ); 
    
    if( $specno != false && $createdSpecNo == false )
    {
      $this->error = true;
      return "Specno exists";
    }

    $last = $this->get_last_barcode();    

    if( $last == false )
    {
      $this->checkout_barcode( sprintf( "%s%03d" , "AA", 1 ), $insspec );
      return sprintf( "%s%03d" , "AA", 1 );
    }

    $letter1 = $last[0];
    $letter2 = $last[1];
    $num = substr( $last , 2 );
    $nextnum = $num + 1;
    $prefix = substr( $last , 0 , 2 );

    if( $nextnum > 999 || $specno != false )
    {
      $nextnum = 1;
      $nextletter = $this->next_letter( $letter2 );	

      if( $nextletter == false )
      {
        $nextletter = $this->next_letter( $letter1 );	

        if( $nextletter == false )
        {
          return "Letter prefixes exhausted";
        }

        $prefix = $nextletter . "A";		
      }
      else
      {
        $prefix = $letter1 . $nextletter;  
      }	  
    }	

    $this->checkout_barcode( sprintf( "%s%03d", $prefix, $nextnum ), $insspec );

    return sprintf( "%s%03d", $prefix, $nextnum );
  }
}
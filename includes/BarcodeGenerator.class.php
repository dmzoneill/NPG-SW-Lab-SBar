<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

class BarcodeGenerator implements IBarcodeGenerator
{
  protected $dbo = null;
  protected $tablename = null;
  protected $userid = -1;
  protected $error = false;
  
  public function __construct()
  {
    $this->dbo = Factory::DB();
  }
  
  protected function create_tables()
  {    
    if( defined( '__DBTYPE__' ) == false || __DBTYPE__ == "mysql" )
    {    
      $createtable = "CREATE TABLE IF NOT EXISTS `" . __TABLE_PREFIX__ . "_user` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user` varchar(10) NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `user` (`user`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
    }
    else
    {
      //sqlite
      $createtable = "CREATE TABLE IF NOT EXISTS `" . __TABLE_PREFIX__ . "_user` (
                    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                    `user` varchar(10) NOT NULL UNIQUE
                    );";
    }
    
    $result = $this->dbo->query( $createtable );	
        
    if( $result == false )
    {
      print_r( $this->dbo->get_error() );
      die( 'Unable to create table barcode_user' );
    }   
    
    $this->get_user();
  }  
  
  protected function get_user()
  {
    $createuser = "INSERT INTO `" . __TABLE_PREFIX__ . "_user` ( `id` , `user` ) VALUES ( NULL , '" . USER . "' );";
    $this->dbo->query( $createuser );	    
    
    $getuser = "SELECT id FROM `" . __TABLE_PREFIX__ . "_user` WHERE user='" . USER . "'";  
    $ps = $this->dbo->query( $getuser );	
    $this->userid = $ps->fetchColumn(); 
  }
  
  protected function next_letter( $letter )
  {
	  $letters = "ABCDEFGHJKLMNPQRSTUVWXYZ";
	  $index = strrpos( $letters , $letter ) + 1;
	  if( $index >= strlen( $letters ) ) return false;
	  return $letters[ $index ];			
  }
  
  protected function rowcount()
  {
	  $all = "SELECT count(*) FROM ". $this->tablename;	
	  $all = $this->dbo->query( $all );    
	  return $all->fetchColumn();
  }
  
  protected function get_last_barcode()
  {
    $last = "SELECT barcode FROM ". $this->tablename . " ORDER BY barcode DESC LIMIT 1;";
    
    $ps = $this->dbo->query( $last );
    $ps = $ps->fetchColumn();	
    
    return $ps;  
  }
  
  public function get_barcodes( $amount = 1 , $specno = false )
  {
    if( $amount < 1 )
    {
      return "";
    }  
    if( $amount > 100 )
    {
      $amount = 100;
    } 
    
    $codes = array();
    
    for( $i = 0; $i < $amount; $i++ )
    {
      $next = $this->get_next_barcode( $specno );
      
      if( $this->error == true )
      {
        return array( $next );
      }
      
      $codes[] = $next;
      $specno = false;
    }	  
    
    return $codes;
  }
  
  private function get_users()
  {
    $arr = array();
	  $all = "SELECT * FROM `" . __TABLE_PREFIX__ . "_user`";	
	  $all = $this->dbo->query( $all );
    
    foreach( $all as $user )
    {
      $arr[ $user['id'] ] = $user[ 'user' ];
    }
    
	  return $arr;
  }
  
  private function get_specnos()
  {
    $arr = array();
	  $all = "SELECT * FROM `" . __TABLE_PREFIX__ . "_specno`;";	
	  $all = $this->dbo->query( $all );
    
    if( __DBTYPE__ != "mysql" )
    {
      $count = "SELECT count(*) FROM `" . __TABLE_PREFIX__ . "_specno`;";
      $count = $this->dbo->query( $count );
      if( $count == false )
      {
        $count = 0;
      }
      else
      {
        $count = $count->fetchColumn();
      }
    }
    else
    {
      $count = $all->rowCount();
    }
    
    if( $all == false || ( $all->rowCount() == 0 && $count == 0 ) )
    {      
      return $arr;
    }
    
    foreach( $all as $specno )
    {
      $arr[ $specno['id'] ] = $specno[ 'specno' ];
    }
    
	  return $arr;
  }
  
  public function listrecords( $draw , $start = 0 , $len = 10 , $search = "", $order = false )
  {
    $dir = is_array( $order ) ? $order[0]['dir'] : "desc";
    $all = "SELECT * FROM ". $this->tablename . " ORDER BY barcode $dir LIMIT $start, $len";	
    $all = $this->dbo->query( $all );
	
    $ret = array();
    $ret[ 'draw' ] = $draw;
    $ret[ 'recordsTotal' ] = $this->rowcount();
    $ret[ 'recordsFiltered' ] = $this->rowcount();
    $ret[ 'data' ] = array();
    $userids = $this->get_users();
    $specsnos = $this->get_specnos();
        
    foreach( $all->fetchAll() as $colcollection )
    {    
      $obj = array();            
      foreach( $colcollection as $colindex => $colvalue )
      {
        if( is_int( $colindex ) ) continue;        
        if( 'id' == $colindex ) continue;
        
        if( 'created' == $colindex )
        {
          $obj[] = date("F j, Y, g:i a" , $colvalue );
        }
        else if( 'creator' == $colindex )
        {
          $obj[] = $userids[ $colvalue ];
        }
        else if( 'specno' == $colindex )
        {
          $obj[] = $specsnos[ $colvalue ];
        }
        else
        {
          $obj[] = $colvalue;
        }        
      }
		
      $ret['data'][] = $obj;  
    }    
    
    return $ret; 
  }
}

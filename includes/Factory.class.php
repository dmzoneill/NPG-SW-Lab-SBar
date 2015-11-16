<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

include( __PATH__ . "/includes/Idbo.php" );
include( __PATH__ . "/includes/IBarcodeGenerator.php" );
include( __PATH__ . "/includes/Ldap.class.php" );
include( __PATH__ . "/includes/DB.class.php" );
include( __PATH__ . "/includes/SqliteDB.class.php" );
include( __PATH__ . "/includes/MysqlDB.class.php" );
include( __PATH__ . "/includes/BarcodeGenerator.class.php" );
include( __PATH__ . "/includes/DutSerials.class.php" );
include( __PATH__ . "/includes/Hvpip.class.php" );
include( __PATH__ . "/includes/Page.class.php" );

class Factory
{
  private static $ldapInstance = null;
  private static $dbInstance = null;
  private static $generatorInstance = null;
  private static $get = array();
  private static $post = array();
  private static $request = array();
  
  public static function DB( $type = "mysql" )
  {    
    if( defined( '__DBTYPE__' ) )
    {
      $type = __DBTYPE__;
    }
    
    if( self::$dbInstance == null )
    {
      self::$dbInstance = ( $type == "mysql" ) ? new MysqlDB(): new SqliteDB();
    }    
    
    return self::$dbInstance;
  }
  
  public static function Ldap()
  {    
    if( self::$ldapInstance == null )
    {
      self::$ldapInstance = Ldap::getInstance();;
    }    
    
    return self::$ldapInstance;
  }
  
  public static function Barcode( $type = "dut" )
  {    
    if( self::$generatorInstance == null )
    {
      self::$generatorInstance = ( $type == "dut" ) ? new DutSerials(): new Hvpip();
    }    
    
    return self::$generatorInstance;
  }
  
  public static function Page( $page = false )
  {    
    return new Page( $page );
  }
  
  public static function Get( $key , $default )
  {    
    return isset( self::$get[ $key ] ) ? self::$get[ $key ] : $default;
  }
  
  public static function Post( $key , $default )
  {    
    return isset( self::$post[ $key ] ) ? self::$post[ $key ] : $default;
  }
  
  public static function Request( $key , $default )
  {    
    return isset( self::$request[ $key ] ) ? self::$request[ $key ] : $default;
  }
  
  private static function Clean()
  {
    foreach( $_GET as $key => $value )
    {
      // todo: sanitize
      self::$get[ $key ] = $value;
    }
    
    foreach( $_POST as $key => $value )
    {
      // todo: sanitize
      self::$post[ $key ] = $value;
    }
    
    foreach( $_REQUEST as $key => $value )
    {
      // todo: sanitize
      self::$request[ $key ] = $value;
    }
    
    unset( $_GET );
    unset( $_POST );
    unset( $_REQUEST );
  }
  
  public static function Authorize()
  {
    Factory::Clean();
    
    if( Factory::Get( "logout" , false ) )
    {
      Factory::Ldap()->logout();
    }

    if( Factory::Post( "username" , false ) && Factory::Post( "password" , false ) )
    {	
      Factory::Ldap()->login( Factory::Post( "username" , false ) , Factory::Post( "password" , false ) );
    }

    define( "USER" , Factory::Ldap()->samaccountname == false ? "Guest" :  Factory::Ldap()->samaccountname );
    define( "DISPLAYNAME" , Factory::Ldap()->displayname == false ? "Guest" :  Factory::Ldap()->displayname );
    define( "USERIMAGE" , Factory::Ldap()->getuserimage( USER ) );
  
    if( __PAGE__ != "login.php" )
    {	
      if( Factory::Ldap()->isLoggedin() == false )
      {		
        header( "Location:login.php" );
        exit;
      }

      if( Factory::Ldap()->isAdmin() == false )
      {
        header( "Location:login.php?error=Insufficient Privileges" );
        exit;
      }
    }
    
    Factory::Ajax();
  }
  
  public static function Ajax()
  {
    if( Factory::Request( "HVPIPBarcodeCreateAmount" , false ) )
    {
      $ds = Factory::Barcode( "hvpip" );
      $newspec = Factory::Request( "HVPIPBarcodeSpecno" , "" );
      $newspec = strlen( $newspec ) > 0 ? $newspec : false;
      print "<tr><td>" . implode( "</td></tr><tr><td>", $ds->get_barcodes( Factory::Request( "HVPIPBarcodeCreateAmount" , 0 ) , $newspec ) ) . "</td></tr>";
      exit;
    }

    if( Factory::Request( "HVPIPGetBarcodes" , false ) )
    {
      $ds = Factory::Barcode( "hvpip" );
      $draw = Factory::Get( "draw" , 0 );
      $start = Factory::Get( "start" , 0 );
      $length = Factory::Get( "length" , 10 );
      $search = Factory::Get( "search" , "" );
      $order = Factory::Get( "order" , false );
      
      print json_encode( $ds->listrecords( $draw, $start, $length, $search, $order ) );
      exit;
    }

    if( Factory::Request( "DutBarcodeCreateAmount" , false ) )
    {
      $ds = Factory::Barcode( "dut" );
      print "<tr><td>" . implode( "</td></tr><tr><td>", $ds->get_barcodes( Factory::Request( "DutBarcodeCreateAmount" , 0 ) ) ) . "</td></tr>";
      exit;
    }

    if( Factory::Request( "DutGetBarcodes" , false ) )
    {
      $ds = Factory::Barcode( "dut" );
      $draw = Factory::Get( "draw" , 0 );
      $start = Factory::Get( "start" , 0 );
      $length = Factory::Get( "length" , 10 );
      $search = Factory::Get( "search" , "" );
      $order = Factory::Get( "order" , false );
      
      print json_encode( $ds->listrecords( $draw, $start, $length, $search, $order ) );
      exit;
    }
  }
}
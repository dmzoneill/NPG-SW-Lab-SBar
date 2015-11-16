<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

class Page
{
  private $contents = "";
  private $header = "";
  private $footer = "";
  
  public function __construct( $file = false )
  {
    if( $file != false )
    {      
      $this->contents = file_get_contents( "templates/" . $file . ".html" ); 
    }
    
    $this->header = file_get_contents( "templates/header.html" );     
    $this->footer = file_get_contents( "templates/footer.html" );   
  }
  
  public function replace( $target , $replacement )
  {
    $this->contents = preg_replace( "/$target/" , $replacement , $this->contents );
  }
  
  public function show()
  {    
    $this->contents = $this->header . $this->contents . $this->footer;
    
    $this->replace( "USERIMAGE" , USERIMAGE );
    $this->replace( "USER" , DISPLAYNAME );
    $this->replace( "TITLE" , __NAME__ );
    $this->replace( "DESCRIPTION" , __DESCRIPTION__ );
    $this->replace( "MENU" , ( Factory::Ldap()->isLoggedin() == true && Factory::Ldap()->isAdmin() == true ) ? file_get_contents( "templates/menu.html" ) : "" );
    
    print $this->contents;
  }
}
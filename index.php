<?php

include( "config.php" );

$page = Factory::Get( "page" , "index" );

switch( $page )
{
  case "dutserials": 
    Factory::Page( "dutserials" )->show();
  break;
  
  case "hvpip": 
    Factory::Page( "hvpip" )->show();
  break; 
  
  default: 
    Factory::Page()->show();
  break; 
}

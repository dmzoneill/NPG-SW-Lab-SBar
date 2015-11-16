<?php

//
// Error Reporting 
//

ini_set( "display_errors", 1 );  // 0 to turn off
ini_set( "error_reporting" , -1 ); // 0 to turn off

//
// App General
//

define( "__NAME__" , "SBar" );
define( "__DESCRIPTION__" , "Something about serials, barcodes and asset management" );

//
// Database
//

define( "__DBTYPE__" , "mysql" ); // mysql or (sqlite needs work) 
define( "__DBHOST__" , "localhost" );
define( "__DBUSER__" , "root" ); 
define( "__DBPASS__" , "tester" ); 
define( "__DBNAME__" , "davetest" ); 
define( "__TABLE_PREFIX__" , "SBar" ); 

//
// Ldap
//

define( "__LDAP_CONTROLLER__" , "ldaps://ger.corp.intel.com" ); 
define( "__LDAP_PORT__" , 636 ); // 3269 global catalog
define( "__LDAP_USERS_BASEDN__" , "DC=ger,DC=corp,DC=intel,DC=com" );
define( "__LDAP_UPN_DOMAIN__" , "@ger.corp.intel.com" );
define( "__LDAP_USER_GROUP_MEMBEROF__" , "CN=SIE All,OU=Delegated,OU=Groups," . __LDAP_USERS_BASEDN__ . 
                                         ";CN=SHN Lab Support and Admins,OU=Delegated,OU=Groups," . __LDAP_USERS_BASEDN__ . 
                                         ";OU=Generic-Account,OU=Resources," . __LDAP_USERS_BASEDN__ .
                                         ";OU=Service-Accounts,OU=Engineering Computing,OU=Resources," . __LDAP_USERS_BASEDN__ . 
                                         ";CN=Domain Users,CN=Users," . __LDAP_USERS_BASEDN__ ); // colon ; separated

//
// No Edit beyond here
//

define( "__INIT__" , true );
define( "__PAGE__" , substr( $_SERVER[ "URL" ] , strrpos( $_SERVER[ "URL" ] , "/") + 1 ) );
define( "__PATH__" , str_replace( "\\" , "/" , realpath( dirname( __FILE__ ) ) ) );

include( __PATH__ . "/includes/Factory.class.php" );
Factory::Authorize();

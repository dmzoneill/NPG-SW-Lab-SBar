<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

class Ldap
{
  private static $instance = null;
	private $ldap_controller = __LDAP_CONTROLLER__;
  private $ldap_controller_port = __LDAP_PORT__;
	private $ldap_base_dn = __LDAP_USERS_BASEDN__;
	private $ldap_upn_domain = __LDAP_UPN_DOMAIN__;
	private $ldap_connection = NULL;
	private $error = 0;
	private $ldap_admin_groups = array();	
	private $search_attributes = null;
	private $user_obj = null;
	private $user_properties = null;
	protected $userimages = array();

	private function __construct()
	{
    if( defined( '__LDAP_USER_GROUP_MEMBEROF__' ) )
    {
      if( strpos( __LDAP_USER_GROUP_MEMBEROF__ , ';' ) !== false )
      {
        $this->ldap_admin_groups = explode( ";" , __LDAP_USER_GROUP_MEMBEROF__ );
      }
      else
      {
        $this->ldap_admin_groups[] = __LDAP_USER_GROUP_MEMBEROF__;
      }
    }
  
		session_start();
	}

	private function connect()
	{
		$connected = false;
		$count = 0;
		
		while( $connected == false && $count < 5 )
		{			
			$this->ldap_connection = ldap_connect( $this->ldap_controller , $this->ldap_controller_port );

			if( !$this->ldap_connection )
			{
				$this->error = ldap_error( $this->ldap_connection );				
			}
			else
			{
				$connected = true;
			}
			
			$count++;
		}

		if( $connected )
		{
			ldap_set_option( $this->ldap_connection , LDAP_OPT_PROTOCOL_VERSION , 3 );
			ldap_set_option( $this->ldap_connection , LDAP_OPT_REFERRALS , 0 );
		}
				
		return $connected;
	}
	
	public function login( $user , $pass )
	{
		if( $this->connect() == true )
		{			
			if( @ldap_bind( $this->ldap_connection , $user . $this->ldap_upn_domain , $pass ) === TRUE )
			{				
				$this->search_attributes = array( "*" );	
				$filter = "(sAMAccountName=$user)";
				$this->user_obj = ldap_search( $this->ldap_connection , $this->ldap_base_dn , $filter , $this->search_attributes );
								
				if( $this->user_obj == false ) 
				{
					header( "Location: login.php?error=Unable to find user" );
					exit;
				}
				
				$this->user_properties = ldap_get_entries( $this->ldap_connection , $this->user_obj );	
								
				if( $this->user_properties == false ) 
				{
					header( "Location: login.php?error=Unable to get user properties" );
					exit;
				}
				
				$this->user_properties = array_change_key_case( $this->user_properties[0] );				
					
				$_SESSION[ 'loggedin' ] = true;			
				$_SESSION[ 'type' ] = $this->isinadmingroup() == true ? "admin" : "user";		
				$_SESSION[ 'user_properties' ] = $this->user_properties;
				$_SESSION[ 'upn' ] = $this->userprincipalname;
							
				$this->getimage();
			
				ldap_unbind( $this->ldap_connection );
			}
			else
			{				
				$_SESSION[ 'loggedin' ] = false;
				$this->error = ldap_error( $this->ldap_connection );
				header( "Location: login.php?error=" . $this->error );
				exit;
			}	
		}
		else
		{
			header( "Location: login.php?error=" . $this->error );
			exit;
		}
	}

	private function isinadmingroup()
	{
		if( is_array( $this->memberof ) == false )
		{
			print_r( $this->user_properties );
			exit;
			if( in_array( $this->memberof , $this->ldap_admin_groups ) )
			{
				return true;
			}
			
			return false;			
		}
		
		foreach( $this->memberof as $group )
		{
			if( in_array( $group , $this->ldap_admin_groups ) )
			{
				return true;
			}
		}	
		
		return false;
	}

	public function getuser()
	{
        return $_SESSION[ 'username' ];
	}
	
	public function logout()
	{
		$_SESSION[ 'loggedin' ] = false;
	}

	public function geterror()
	{
		return $this->error;
	}
	
	public function isLoggedin()
	{
		return isset( $_SESSION[ 'loggedin' ] ) ? $_SESSION[ 'loggedin' ] : false;
	}

	public function isAdmin()
	{
		return $_SESSION[ 'type' ] == "admin" ? true : false;
	}	

	public function __get( $property )
	{		
		if( $this->isLoggedin() == true )
		{
			if( $this->user_properties == null )
			{
				$this->user_properties = $_SESSION[ 'user_properties' ];				
			}
			
			if( array_key_exists( $property , $this->user_properties ) ) 
			{
				if( $this->user_properties[ $property ][ 'count' ] > 1 )
				{
					return $this->user_properties[ $property ];
				}

				return $this->user_properties[ $property ][ 0 ];
			}

			return false;
		}
		
		return false;
	}
	
	public function getimage()
	{			
		$this->userimages = glob( "images/users/*" );
		
		$img = @imagecreatefromstring( $this->thumbnailphoto );		
		@imagejpeg( $img , "images/users/" . $this->samaccountname . ".jpg" );
	}	
	
	public function getuserimage( $idsid )
	{				
		$this->userimages = glob( "images/users/*" );
		
		if( in_array( "images/users/" . $idsid . ".jpg" , $this->userimages ) )
		{
			return "images/users/" . $idsid . ".jpg";
		}
		else
		{
			return "images/person.png";
		}
	}

  public static function getInstance()
  {
    if( self::$instance == null )
    {
      self::$instance = new Ldap();
    }    
    
    return self::$instance;
  }
}
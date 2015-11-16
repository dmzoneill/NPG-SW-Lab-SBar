<?php

include( "config.php" );

$page = Factory::Page( "login" );
$page->replace( "ERROR" , Factory::Request( "error" , "" ) );
$page->show();
	
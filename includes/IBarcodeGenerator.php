<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

interface IBarcodeGenerator
{
  public function get_barcodes( $amount = 1 , $specno = false );
  public function listrecords( $draw , $start = 0 , $len = 10 , $search = "", $order = false );
}
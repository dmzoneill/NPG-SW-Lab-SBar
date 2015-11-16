<?php

if(!defined( '__INIT__' ) ) die('Direct access not permitted');

interface Idbo
{
  public function query( $sql );
  public function is_connected();
  public function disconnect();
  public function connect();
  public function get_error();
}
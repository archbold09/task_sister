<?php
require_once __DIR__."/../config/connection.php";
/**
 * Modelo de tiempo
 */
class user extends Connection {
	private $list;

	function __construct() {
		parent::__construct();
	}

	public function consultUser( $params ) {
		$sentence  = $this->runWithParams("SELECT * FROM users WHERE username = :username AND password = :password",$params);
		$this->list = $sentence ->fetchAll( PDO::FETCH_ASSOC );
		return $this->list;
	}

}

<?php 

define ("DB_USER","user_geral");
define ("DB_PASS","dg3000");
define ("DSN","sqlsrv:server=191.241.142.60;database=votorantim");

class Conexao {

	private static $pdo;


	public static function getInstance() {
	    if(!self::$pdo) {
	        try{
	            self::$pdo = new PDO(DSN,DB_USER, DB_PASS);
	            self::$pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
	            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        } catch (PDOException $ex) {
	            die("Erro na conexão: ".$ex->getMessage()."<br/>");
	        }
	    }
    	return self::$pdo;
    }
}



<?php 

class factoryImportacao{

	public static function getInstance($padrao){

		switch ($padrao):
			case 'encontra':
				require_once "conexao.php";
				require_once "encontra.class.php";
				$pdo = Conexao::getInstance();
				return new encontra($pdo, 50000);

				break;

			case 'guiamais':
				require_once "conexao.php";
				require_once "guiamais.class.php";
				$pdo = Conexao::getInstance();
				return new guiamais($pdo, 50000);

				break;
			
			default:
				return null;
				break;
		endswitch;
		
	}

}

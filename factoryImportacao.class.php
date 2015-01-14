<?php 

require_once "../../../conexao.php";

class factoryImportacao{

	public static function getInstance($padrao, $idImportacao){

		switch ($padrao):
			case 'encontra':
				require_once "encontra.class.php";
				$pdo = Conexao::getInstance();
				return new encontra($pdo, $idImportacao);

				break;

			case 'guiamais':
				require_once "guiamais.class.php";
				$pdo = Conexao::getInstance();
				return new guiamais($pdo, $idImportacao);

				break;

			case 'apontador':
				require_once "apontador.class.php";
				$pdo = Conexao::getInstance();
				return new apontador($pdo, $idImportacao);

				break;
			
			default:
				return null;
				break;
		endswitch;
		
	}

}

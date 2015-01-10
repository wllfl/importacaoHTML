<?php 
header('Content-Type: text/html; charset=utf-8');  

require_once "factoryImportacao.class.php";
//require_once "importacao.class.php";
//require_once "encontra.class.php";
//require_once "guiamais.class.php";

//$pdo = Conexao::getInstance();
//$importacao = new encontra($pdo, 1000);
$importacao = factoryImportacao::getInstance("encontra");
//$importacao->parseHTML("http://www.encontravotorantim.com.br/l/lanchonete-em-votorantim.shtml", true);
$importacao->parseHTML("http://www.guiamais.com.br/busca/pizzaria-votorantim", true);

//function converteTexto($nome){
	//$nome = trim(strtr($nome,"ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß", "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ"));
	//$palavra = explode(" ",$nome);
	//$nomeconvertido = "";
	//$arrayUPPER = array("À","Á","Â","Ã","Ä","Å","Æ","Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ð","Ñ","Ò","Ó","Ô","Õ","Ö","×","Ø","Ù","Ü","Ú","Þ","ß");
	//$arrayLOWER = array("à","á","â","ã","ä","å","æ","ç","è","é","ê","ë","ì","í","î","ï","ð","ñ","ò","ó","ô","õ","ö","÷","ø","ù","ü","ú","þ","ÿ");
//
	//for ($i=0; $i < count($palavra) ; $i++):
	    //if ($palavra[$i] != "da" && $palavra[$i] != "de" && $palavra[$i] != "do" && $palavra[$i] != "das" && $palavra[$i] != "dos"):
	       //$palavra[$i] = ucwords($palavra[$i]);
	       //$primeira  = substr($palavra[$i], 0, 1);
	       //echo strlen($palavra[$i])  . "<br>";
	       //$resto  = substr($palavra[$i], 1, 100);
	       ////$primeira = str_replace($primeira,strtr($primeira,"àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ","ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß"),$primeira);
	       //$primeira = str_replace($arrayLOWER, $arrayUPPER, $primeira);
//
	       //$palavra[$i] = $primeira.$resto;
	    //endif;
//
	    //$nomeconvertido = $nomeconvertido." ".$palavra[$i];
	//endfor;
//
	//return $nomeconvertido;
//}
//
//$texto = "Área com traço e acentuação";
//echo converteTexto($texto);
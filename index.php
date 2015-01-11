<?php 
header('Content-Type: text/html; charset=utf-8');  

require_once "factoryImportacao.class.php";

$importacao = factoryImportacao::getInstance("encontra", 3000);
$importacao->parseHTML("http://www.encontravotorantim.com.br/l/lanchonete-em-votorantim.shtml", true);
//$importacao->parseHTML("http://www.guiamais.com.br/busca/pizzaria-votorantim", true);


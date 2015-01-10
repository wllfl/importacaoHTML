<?php 

require_once "conexao.php";
require_once "importacao.class.php";
require_once "encontra.class.php";
require_once "guiamais.class.php";

$pdo = Conexao::getInstance();
$importacao = new guiamais($pdo, 1000);
//$importacao->parseHTML("http://www.encontravotorantim.com.br/p/pizzaria-em-votorantim.shtml");
$importacao->parseHTML("http://www.guiamais.com.br/busca/pizzaria-votorantim");
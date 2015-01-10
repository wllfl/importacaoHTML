<?php 

require_once "conexao.php";
require_once "importacao.class.php";
require_once "encontra.class.php";
require_once "guiamais.class.php";

$pdo = Conexao::getInstance();
$importacao = new encontra($pdo, 11);
$importacao->parseHTML("", true);
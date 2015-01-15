<?php 
header('Content-Type: text/html; charset=utf-8');  

require_once "factoryImportacao.class.php";

$importacao = factoryImportacao::getInstance("apontador", 9000);
$importacao->parseHTML("http://www.apontador.com.br/local/search.html?q=bares&loc_z=votorantim&loc=Votorantim%2C+SP&loc_y=Votorantim%2C+SP", true);



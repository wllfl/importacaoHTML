<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time','-1');
header('Content-Type: text/html; charset=utf-8');  

require_once "importacao.class.php";
require_once "simple_html_dom.php";

class encontra extends importacao{

	public function __construct($conexao=null, $idImportacao){
		parent::__construct($conexao, $idImportacao);
	}

	/***********************************************************************************************************************************/

	public function parseHTML($url, $exibeSaida=false){

		try{
			if($this->isPadraoCorretoHTML($url)):
				if ($this->isHTMLTempGravado($url)):
					$this->limpaHTML();
				
					if($this->isBoxUnico()):
						$this->processaLayoutBoxUnico($exibeSaida);
					else:
						$this->processaLayoutVariosBox($exibeSaida);
					endif;
					$this->finalizaImportacao();
				endif; 
			else:
				$dados = parse_url($url);
				$urlPesquisada = $dados['host'];
				echo "<h1>Está URL ({$urlPesquisada}) não pode ser processada com o padrão informado \"encontra\"!</h1>";
				exit();
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function isPadraoCorretoHTML($url){
		try{
			$contadoURL = substr_count($url, "encontra");

			if ($contadoURL > 0):
				return true;
			else:
				return false;
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function limpaHTML(){
		try{
			$html = file_get_contents("temp.html", true);
			$htmlSemCabecalho = $this->limpaCabecalho($html);

			unlink("temp.html");
			$handle = fopen("temp.html", "w");
			fwrite($handle, $htmlSemCabecalho);
			fclose($handle);

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function limpaCabecalho($html){
		try{
			$arraySemCabecalho = explode("<div id=\"txt1miolo\">", $html);

			return $arraySemCabecalho[1];
		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function isBoxUnico(){
		try{
			$html = file_get_contents("temp.html", true);
			$contadoBox = substr_count($html, "class=\"style8\"");

			if ($contadoBox > 1):
				return false;
			else:
				return true;
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function processaLayoutBoxUnico($exibeSaida){
		try{
			$pagina = file_get_contents("temp.html", true);
			$html = str_get_html($pagina);

			$arrayHTMLComParagrafo = $html->find('div[class=style8] p');

			if (count($arrayHTMLComParagrafo) == 0):
				$this->htmlUnicoAnuncio($exibeSaida);
			else:
				$this->htmlVariosAnuncio($exibeSaida);
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function htmlUnicoAnuncio($exibeSaida){
		try{
			$contAnuncio = 0;
			$pagina = file_get_contents("temp.html", true);
			$html = str_get_html($pagina);

			foreach($html->find('div[class=style8]') as $element):
				if ($contAnuncio >= $this->limteAnuncio) break;

				$titulo = "";
				$endereco = "";
				$numero = "";
				$bairro = "";
				$cidade = "";
				$uf = "";
				$cep = "";
				$fone = "";

				$hTitulo = $element->find('span[class=style4]');
				$titulo   = (!empty($hTitulo)) ? strip_tags($hTitulo[0]) : '';

				$arrayTituloEnderecoFone = explode("<br />", $element);
				$arrayRuaNumeroBairroCidadeCep = explode(" - ", $arrayTituloEnderecoFone[1]);

				if(count($arrayRuaNumeroBairroCidadeCep) > 1):
					$arrayRuaNumero = explode(",", $arrayRuaNumeroBairroCidadeCep[0]);
					$endereco = (!empty($arrayRuaNumero[0])) ? strip_tags($arrayRuaNumero[0]) : '';
					$numero   = (!empty($arrayRuaNumero[1])) ? strip_tags($arrayRuaNumero[1]) : '';
					$bairro   = (!empty($arrayRuaNumeroBairroCidadeCep[1])) ? str_replace("Bairro:", "", strip_tags($arrayRuaNumeroBairroCidadeCep[1])) : '';
					$cidade   = (!empty($arrayRuaNumeroBairroCidadeCep[2])) ? strip_tags($arrayRuaNumeroBairroCidadeCep[2]) : '';
					$uf       = (!empty($arrayRuaNumeroBairroCidadeCep[3])) ? strip_tags($arrayRuaNumeroBairroCidadeCep[3]) : '';
					$cep      = (count($arrayRuaNumeroBairroCidadeCep) > 4 && !empty($arrayRuaNumeroBairroCidadeCep[4])) ? str_replace("CEP:", "", strip_tags($arrayRuaNumeroBairroCidadeCep[4])) : '';
					$fone     = (!empty($arrayTituloEnderecoFone[2])) ? strip_tags($arrayTituloEnderecoFone[2]) : '';
				else:
					$fone     = (!empty($arrayTituloEnderecoFone[1])) ? strip_tags($arrayTituloEnderecoFone[1]) : '';
				endif;
				
				if($exibeSaida):
					$this->imprimeSaida($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, false);
				else:
					$this->insertAnuncio($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone);
				endif;

				unset($titulo);
				unset($endereco);
				unset($numero);
				unset($bairro);
				unset($cidade);
				unset($uf);
				unset($cep);
				unset($fone);
				
				$contAnuncio++;
			endforeach;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function htmlVariosAnuncio($exibeSaida){
		try{
			$contAnuncio = 0;
			$pagina = file_get_contents("temp.html", true);
			$html = str_get_html($pagina);

			foreach($html->find('p') as $element):
				if ($contAnuncio >= $this->limteAnuncio) break;

				$titulo = "";
				$endereco = "";
				$numero = "";
				$bairro = "";
				$cidade = "";
				$uf = "";
				$cep = "";
				$fone = "";

				$hTitulo = $element->find('span[class=style4]');
				$titulo   = (!empty($hTitulo)) ? strip_tags($hTitulo[0]) : '';

				$arrayTituloEnderecoFone = explode("<br />", $element);
				$arrayRuaNumeroBairroCidadeCep = explode(" - ", $arrayTituloEnderecoFone[1]);

				if(count($arrayRuaNumeroBairroCidadeCep) > 1):
					$arrayRuaNumero = explode(",", $arrayRuaNumeroBairroCidadeCep[0]);
					$endereco = (!empty($arrayRuaNumero[0])) ? strip_tags($arrayRuaNumero[0]) : '';
					$numero   = (!empty($arrayRuaNumero[1])) ? strip_tags($arrayRuaNumero[1]) : '';
					$bairro   = (!empty($arrayRuaNumeroBairroCidadeCep[1])) ? str_replace("Bairro:", "", strip_tags($arrayRuaNumeroBairroCidadeCep[1])) : '';
					$cidade   = (!empty($arrayRuaNumeroBairroCidadeCep[2])) ? strip_tags($arrayRuaNumeroBairroCidadeCep[2]) : '';
					$uf       = (!empty($arrayRuaNumeroBairroCidadeCep[3])) ? strip_tags($arrayRuaNumeroBairroCidadeCep[3]) : '';
					$cep      = (count($arrayRuaNumeroBairroCidadeCep) > 4 && !empty($arrayRuaNumeroBairroCidadeCep[4])) ? str_replace("CEP:", "", strip_tags($arrayRuaNumeroBairroCidadeCep[4])) : '';
					$fone     = (!empty($arrayTituloEnderecoFone[2])) ? strip_tags($arrayTituloEnderecoFone[2]) : '';
				else:
					$fone     = (!empty($arrayTituloEnderecoFone[1])) ? strip_tags($arrayTituloEnderecoFone[1]) : '';
				endif;

				if($exibeSaida):
					$this->imprimeSaida($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, false);
				else:
					$this->insertAnuncio($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone);
				endif;

				unset($titulo);
				unset($endereco);
				unset($numero);
				unset($bairro);
				unset($cidade);
				unset($uf);
				unset($cep);
				unset($fone);

				$contAnuncio++;
			endforeach;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function processaLayoutVariosBox($exibeSaida){
		try{
			$contAnuncio = 0;
			$pagina = file_get_contents("temp.html", true);
			$html = str_get_html($pagina);

			foreach($html->find('div[class=style8]') as $element):
				if ($contAnuncio >= $this->limteAnuncio) break;

				$titulo = "";
				$endereco = "";
				$numero = "";
				$bairro = "";
				$cidade = "";
				$uf = "";
				$cep = "";
				$fone = "";

				$hTitulo = $element->find('span[class=style4]');
				$titulo   = (!empty($hTitulo)) ? strip_tags($hTitulo[0]) : '';

				$arrayTituloEnderecoFone = explode("<br/>", $element);
				$arrayRuaNumeroBairroCidadeCep = explode(" - ", $arrayTituloEnderecoFone[1]);

				if(count($arrayRuaNumeroBairroCidadeCep) > 1):
					$arrayRuaNumero = explode(",", $arrayRuaNumeroBairroCidadeCep[0]);
					$endereco = (!empty($arrayRuaNumero[0])) ? strip_tags($arrayRuaNumero[0]) : '';
					$numero   = (!empty($arrayRuaNumero[1])) ? strip_tags($arrayRuaNumero[1]) : '';
					$bairro   = (!empty($arrayRuaNumeroBairroCidadeCep[1])) ? str_replace("Bairro:", "", strip_tags($arrayRuaNumeroBairroCidadeCep[1])) : '';
					$cidade   = (!empty($arrayRuaNumeroBairroCidadeCep[2])) ? strip_tags($arrayRuaNumeroBairroCidadeCep[2]) : '';
					$uf       = (!empty($arrayRuaNumeroBairroCidadeCep[3])) ? strip_tags($arrayRuaNumeroBairroCidadeCep[3]) : '';
					$cep      = (count($arrayRuaNumeroBairroCidadeCep) > 4 && !empty($arrayRuaNumeroBairroCidadeCep[4])) ? str_replace("CEP:", "", strip_tags($arrayRuaNumeroBairroCidadeCep[4])) : '';
					$fone     = (!empty($arrayTituloEnderecoFone[2])) ? strip_tags($arrayTituloEnderecoFone[2]) : '';
				else:
					$fone     = (!empty($arrayTituloEnderecoFone[1])) ? strip_tags($arrayTituloEnderecoFone[1]) : '';
				endif;
				
				if($exibeSaida):
					$this->imprimeSaida($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, false);
				else:
					$this->insertAnuncio($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone);
				endif;

				unset($titulo);
				unset($endereco);
				unset($numero);
				unset($bairro);
				unset($cidade);
				unset($uf);
				unset($cep);
				unset($fone);
				
				$contAnuncio++;
			endforeach;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function limpaRodape($html){
		//
	}

	/***********************************************************************************************************************************/

	protected function isPossuiPaginacao(){
		///
	}

	/***********************************************************************************************************************************/

	protected function getHTMLFimAnuncios(){
		///
	}

}

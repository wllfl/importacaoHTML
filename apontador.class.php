<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time','-1');
header('Content-Type: text/html; charset=utf-8');  

require_once "importacao.class.php";
require_once "simple_html_dom.php";

class apontador extends importacao{

	private $URL_BASE = "http://www.apontador.com.br";


	public function __construct($conexao=null, $idImportacao){
		parent::__construct($conexao, $idImportacao);
	}

	/***********************************************************************************************************************************/

	public function parseHTML($url, $exibeSaida=false){

		try{
			if($this->isPadraoCorretoHTML($url)):
				if ($this->isHTMLTempGravado($url)):
						$this->limpaHTML();

						if ($this->isPossuiPaginacao()):	
							$this->processaLayoutComPaginacao($url, $exibeSaida);
						else:
							$this->processaLayoutSemPaginacao($exibeSaida);
						endif;

						if(!$exibeSaida) $this->finalizaImportacao();
				endif; 
			else:
				$dados = parse_url($url);
				$urlPesquisada = $dados['host'];
				echo "<h1>Está URL ({$urlPesquisada}) não pode ser processada com o padrão informado \"apontador\"!</h1>";
				exit();
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function isPadraoCorretoHTML($url){
		try{
			$contadoURL = substr_count($url, "apontador");

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
			$htmlSemRodape = $this->limpaRodape($htmlSemCabecalho);

			unlink("temp.html");
			$handle = fopen("temp.html", "w");
			fwrite($handle, $htmlSemRodape);
			fclose($handle);

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function limpaCabecalho($html){
		try{
			$arraySemCabecalho = explode("<section class=\"js-result-box\">", $html);

			return $arraySemCabecalho[1];
		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function limpaRodape($html){
		try{
			$arraySemRodape = explode("<footer class=\"footer\">", $html);
			return $arraySemRodape[0];

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function isPossuiPaginacao(){
		try{

			if(file_exists("temp.html")):
				$html = file_get_contents("temp.html", true);
				$contadorPag = substr_count($html, "<nav class=\"pagination\">");

				if ($contadorPag > 0):
					return true;
				else:
					return false;
				endif;
			else:
				return false;
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function processaLayoutSemPaginacao($exibeSaida){
		try{
			$contAnuncio = 0;
			$strTemp = file_get_contents("temp.html", true);
			$html = str_get_html($strTemp);

			foreach($html->find('article[class=poi card]') as $element):

				if ($contAnuncio > $this->limteAnuncio) break;

				$titulo = "";
				$endereco = "";
				$numero = "";
				$bairro = "";
				$cidade = "";
				$uf = "";
				$cep = "";
				$fone = "";

				$anuncio        = str_get_html($this->file_get_contents_curl($element->href));
				$hTitulo        = $anuncio->find('header[class=poi-header] h1[itemprop=name]');
				$hRuaNumero     = $anuncio->find('span[itemprop=streetAddress]');
				$hBairro        = $anuncio->find('li[itemprop="address]');
				$hCidadeUfCep   = $anuncio->find('span[itemprop=addressLocality]');
				$hFone          = $anuncio->find('li[class=item info-icon info-icon-phone] strong');

				$arrayBairro    = (!empty($hBairro)) ? explode(", ", $hBairro[0]): ''; 
				$stringTemp     = (!empty($hCidadeUfCep)) ? str_replace(", CEP:", " - ", strip_tags($hCidadeUfCep[0])) : '';
				$arrayCidadeUfCep = explode(" - ", $stringTemp); 					
				$arrayRuaNumero = (!empty($hRuaNumero)) ? explode(",", $hRuaNumero[0]): '';

				if (count($arrayBairro) > 4):
					$bairroTratado  = (!empty($arrayBairro[3])) ? str_replace(array('SP', '-', 'Votorantim', '—', 'Como chegar'), "", strip_tags($arrayBairro[3])) : '';
				else: 
					$bairroTratado  = (!empty($arrayBairro[2])) ? str_replace(array('SP', '-', 'Votorantim', '—', 'Como chegar'), "", strip_tags($arrayBairro[2])) : '';
				endif;


				$titulo      = (!empty($hTitulo)) ? strip_tags($hTitulo[0]) : '';
				$endereco    = (!empty($arrayRuaNumero[0])) ? strip_tags($arrayRuaNumero[0]) : ''; 
				$numero      = (count($arrayRuaNumero) > 1) ? strip_tags($arrayRuaNumero[1]) : '';
				$bairro      = (!empty($bairroTratado)) ? $bairroTratado : '';
				$cidade      = (!empty($arrayCidadeUfCep[0])) ? strip_tags($arrayCidadeUfCep[0]) : '';
				$cep         = (!empty($arrayCidadeUfCep[2])) ? strip_tags($arrayCidadeUfCep[2]) : '';
				$uf          = (!empty($arrayCidadeUfCep[1])) ? strip_tags($arrayCidadeUfCep[1]) : '';
				$dataContent   = "data-content";
				$fone          = (!empty($hFone))   ? strip_tags($hFone[0]->$dataContent) : '';

				if($exibeSaida):
					$this->imprimeSaida($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, true);
				else:
					//$this->insertAnuncio($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, true);
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
			
			if (file_exists("temp.html")) unlink('temp.html');

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function processaLayoutComPaginacao($url, $exibeSaida){
		try{
			$contAnuncio = 0;
			$page = 1;
			$fimLoop = false;

			while (!$fimLoop && $contAnuncio <= $this->limteAnuncio):

				if ($this->isHTMLTempGravado($url . "&page=".$page)):

					$strTemp = file_get_contents("temp.html", true);
					$html = str_get_html($strTemp);
					if(!$this->isPossuiPaginacao()) $fimLoop = true;

					foreach($html->find('article[class=poi card]') as $element):

						if ($contAnuncio >= $this->limteAnuncio) break 2;

						if($this->isEnviaRequisicaoPhone($element)):
							$arrayDados = $this->htmlComAtributo($element);
						else:
							$arrayDados = $this->htmlSemAtributo($element);
						endif;

						$titulo = "";
						$endereco = "";
						$numero = "";
						$bairro = "";
						$cidade = "";
						$uf = "";
						$cep = "";
						$fone = "";
						list($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone) = $arrayDados;

						if($exibeSaida):
							$this->imprimeSaida($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, false);
						else:
							$this->insertAnuncio($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, false);
						endif;

						$contAnuncio++;
					endforeach;
						
					if (file_exists("temp.html")) unlink('temp.html');
				endif;
			endwhile;

			echo "<h1>FINALIZADO {$contAnuncio} - {$fimLoop}</h1>";
		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function isEnviaRequisicaoPhone($html){
		try{

			if(!empty($html)):
				$contadorClass = substr_count($html, "<span class=\"see-phone\">");

				if ($contadorClass > 0):
					return true;
				else:
					return false;
				endif;
			else:
				return false;
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function htmlSemAtributo($element){
		try{
			$titulo = "";
			$endereco = "";
			$numero = "";
			$bairro = "";
			$cidade = "";
			$uf = "";
			$cep = "";
			$fone = "";
			
			$anuncioHtml = $element->find('p[class=poi address]');
			$anuncio = strip_tags($anuncioHtml[0]);
			$arrayEnderecoNumeroBairroCidadeUfCep = explode(", ", str_replace(" - ", ", ", $anuncio));
			$hTitulo = $element->find('h3[class=poi name] a');
			$titulo = ($hTitulo) ? strip_tags($hTitulo[0]) : '';
			$endereco = (!empty($arrayEnderecoNumeroBairroCidadeUfCep[0])) ? strip_tags($arrayEnderecoNumeroBairroCidadeUfCep[0]) : '';
			$numero = (!empty($arrayEnderecoNumeroBairroCidadeUfCep[1])) ? strip_tags($arrayEnderecoNumeroBairroCidadeUfCep[1]) : '';
			$bairro = (!empty($arrayEnderecoNumeroBairroCidadeUfCep[2])) ? strip_tags($arrayEnderecoNumeroBairroCidadeUfCep[2]) : '';
			$cidade = (!empty($arrayEnderecoNumeroBairroCidadeUfCep[3])) ? strip_tags($arrayEnderecoNumeroBairroCidadeUfCep[3]) : '';
			$uf = (!empty($arrayEnderecoNumeroBairroCidadeUfCep[4])) ? strip_tags($arrayEnderecoNumeroBairroCidadeUfCep[4]) : '';
			$cep = (!empty($arrayEnderecoNumeroBairroCidadeUfCep[5])) ? strip_tags($arrayEnderecoNumeroBairroCidadeUfCep[5]) : '';

			$arrayRetorno = array($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone);
			return $arrayRetorno;
		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	private function htmlComAtributo($element){
		try{

			$titulo = "";
			$endereco = "";
			$numero = "";
			$bairro = "";
			$cidade = "";
			$uf = "";
			$cep = "";
			$fone = "";

			$link 	        = $element->find('div[class=poi phone] a');
			$anuncio        = str_get_html($this->file_get_contents_curl($link[0]->href));
			$hTitulo        = $anuncio->find('header[class=poi-header] h1[itemprop=name]');
			$hRuaNumero     = $anuncio->find('span[itemprop=streetAddress]');
			$hBairro        = $anuncio->find('li[itemprop="address]');
			$hCidadeUfCep   = $anuncio->find('span[itemprop=addressLocality]');
			$hFone          = $anuncio->find('li[class=item info-icon info-icon-phone] strong');

			$arrayBairro    = (!empty($hBairro)) ? explode(", ", $hBairro[0]): ''; 
			$stringTemp     = (!empty($hCidadeUfCep)) ? str_replace(", CEP:", " - ", strip_tags($hCidadeUfCep[0])) : '';
			$arrayCidadeUfCep = explode(" - ", $stringTemp); 					
			$arrayRuaNumero = (!empty($hRuaNumero)) ? explode(",", $hRuaNumero[0]): '';

			if (count($arrayBairro) > 4):
				$bairroTratado  = (!empty($arrayBairro[3])) ? str_replace(array('SP', '-', 'Votorantim', '—', 'Como chegar'), "", strip_tags($arrayBairro[3])) : '';
			else: 
				$bairroTratado  = (!empty($arrayBairro[2])) ? str_replace(array('SP', '-', 'Votorantim', '—', 'Como chegar'), "", strip_tags($arrayBairro[2])) : '';
			endif;

			$titulo      = (!empty($hTitulo)) ? strip_tags($hTitulo[0]) : '';
			$endereco    = (!empty($arrayRuaNumero[0])) ? strip_tags($arrayRuaNumero[0]) : ''; 
			$numero      = (count($arrayRuaNumero) > 1) ? strip_tags($arrayRuaNumero[1]) : '';
			$bairro      = (!empty($bairroTratado)) ? $bairroTratado : '';
			$cidade      = (!empty($arrayCidadeUfCep[0])) ? strip_tags($arrayCidadeUfCep[0]) : '';
			$cep         = (!empty($arrayCidadeUfCep[2])) ? strip_tags($arrayCidadeUfCep[2]) : '';
			$uf          = (!empty($arrayCidadeUfCep[1])) ? strip_tags($arrayCidadeUfCep[1]) : '';
			$dataContent   = "data-content";
			$fone          = (!empty($hFone))   ? strip_tags($hFone[0]->$dataContent) : '';

			$arrayRetorno = array($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone);
			return $arrayRetorno;
		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function getHTMLFimAnuncios(){
		///
	}
}

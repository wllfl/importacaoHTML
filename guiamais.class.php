<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time','-1');
header('Content-Type: text/html; charset=utf-8');  

require_once "importacao.class.php";
require_once "simple_html_dom.php";

class guiamais extends importacao{

	private $URL_BASE = "http://www.guiamais.com.br";


	public function __construct($conexao=null, $idImportacao){
		parent::__construct($conexao, $idImportacao);
	}

	/************************************************************************************************/

	public function parseHTML($url, $exibeSaida=false){

		try{
			if($this->isPadraoCorretoHTML($url)):
				if ($this->gravaHTMLTemp($url)):
						$this->limpaHTML();

						if ($this->isPossuiPaginacao()):	
							$this->processaLayoutComPaginacao($exibeSaida);
						else:
							$this->processaLayoutSemPaginacao($exibeSaida);
						endif; 
						$this->finalizaImportacao();
				endif; 
			else:
				$dados = parse_url($url);
				$urlPesquisada = $dados['host'];
				echo "<h1>Está URL ({$urlPesquisada}) não pode ser processada com o padrão informado \"guiamais\"!</h1>";
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/************************************************************************************************/

	protected function isPadraoCorretoHTML($url){
		try{
			$contadoURL = substr_count($url, "guiamais");

			if ($contadoURL > 0):
				return true;
			else:
				return false;
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}


	/************************************************************************************************/

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

	/************************************************************************************************/

	protected function limpaCabecalho($html){
		try{
			$arraySemCabecalho = explode("class=\"banner\"", $html);

			return $arraySemCabecalho[1];
		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/************************************************************************************************/

	protected function limpaRodape($html){
		try{
			$arraySemRodape = explode("class=\"adsListFooter\"", $html);
			return $arraySemRodape[0];

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/************************************************************************************************/

	protected function isPossuiPaginacao(){
		try{
			$html = file_get_contents("temp.html", true);
			$arrayOutraCidade = explode("headerListing headerDistance", $html);

			if (count($arrayOutraCidade) > 1):
				return false;
			else:
				return true;
			endif;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/************************************************************************************************/

	private function processaLayoutSemPaginacao($exibeSaida){
		try{
			$contAnuncio = 0;
			$html = str_get_html($this->getHTMLFimAnuncios()); 

			foreach($html->find('div[class=vcard figra] div[class=advToolsList] li[class=omega2] a') as $element):

				if ($contAnuncio > $this->limteAnuncio) break;

				$anuncio     = str_get_html($this->file_get_contents_curl($this->URL_BASE . $element->href));
				$hTitulo     = $anuncio->find('div[class=adv] h1[itemprop=name]');
				$hLogradouro = $anuncio->find('span[itemprop=streetAddress]');
				$hBairro     = $anuncio->find('span[class=district]');
				$hCidade     = $anuncio->find('span[itemprop=addressLocality]');
				$hUf         = $anuncio->find('span[itemprop=addressRegion]');
				$hCep        = $anuncio->find('span[itemprop=postalCode]');
				$hFone       = $anuncio->find('div[class=listPhone] ul li[class=tel]');


				$temp_string   = (!empty($hLogradouro)) ? strip_tags($hLogradouro[0]) : ''; 
				$arrayEndereco = explode(',', $temp_string);

				$titulo      = (!empty($hTitulo)) ? strip_tags($hTitulo[0]) : '';
				$endereco    = (!empty($arrayEndereco)) ? $arrayEndereco[0] : ''; 
				$numero      = (count($arrayEndereco) > 1) ? $arrayEndereco[1] : '';
				$bairro      = (!empty($hBairro)) ? str_replace(",", "", strip_tags($hBairro[0])) : '';
				$cidade      = (!empty($hCidade)) ? strip_tags($hCidade[0]) : '';
				$cep         = (!empty($hCep))    ? strip_tags($hCep[0]) : '';
				$uf          = (!empty($hUf))     ? strip_tags($hUf[0]) : '';
				$fone        = (!empty($hFone))   ? strip_tags($hFone[0]) : '';

				if($exibeSaida):
					$this->imprimeSaida($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, true);
				else:
					$this->insertAnuncio($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, true);
				endif;

				$contAnuncio++;
			endforeach;
			
			unlink('temp.html');

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/************************************************************************************************/

	private function processaLayoutComPaginacao($exibeSaida){
		try{
			$contAnuncio = 0;
			$page = 1;
			$fimLoop = false;

			while (!$fimLoop && $contAnuncio <= $this->limteAnuncio):

				if ($this->gravaHTMLTemp($url . "?page=".$page)):

					$strTemp = file_get_contents("temp.html", true);
					if ($page > 1):
						$array_html = explode("headerListing headerDistance", $strTemp);
						$str = $array_html[0];
						$html = str_get_html($str); 

						if (count($array_html) > 1):
						   $fimLoop = true;
					    endif;
					else:
						$html = str_get_html($strTemp);
					endif;

					foreach($html->find('div[class=vcard figra] div[class=advToolsList] li[class=omega2] a') as $element):

						if ($contAnuncio >= $this->limteAnuncio) break 2;

						$anuncio     = str_get_html($this->file_get_contents_curl($this->URL_BASE . $element->href));
						$hTitulo     = $anuncio->find('div[class=adv] h1[itemprop=name]');
						$hLogradouro = $anuncio->find('span[itemprop=streetAddress]');
						$hBairro     = $anuncio->find('span[class=district]');
						$hCidade     = $anuncio->find('span[itemprop=addressLocality]');
						$hUf         = $anuncio->find('span[itemprop=addressRegion]');
						$hCep        = $anuncio->find('span[itemprop=postalCode]');
						$hFone       = $anuncio->find('div[class=listPhone] ul li[class=tel]');

						$temp_string   = (!empty($hLogradouro)) ? strip_tags($hLogradouro[0]) : ''; 
						$arrayEndereco = explode(',', $temp_string);								

						$titulo      = (!empty($hTitulo)) ? strip_tags($hTitulo[0]) : '';
						$endereco    = (!empty($arrayEndereco)) ? $arrayEndereco[0] : ''; 
						$numero      = (count($arrayEndereco) > 1) ? $arrayEndereco[1] : '';
						$bairro      = (!empty($hBairro)) ? str_replace(",", "", strip_tags($hBairro[0])) : '';
						$cidade      = (!empty($hCidade)) ? strip_tags($hCidade[0]) : '';
						$cep         = (!empty($hCep))    ? strip_tags($hCep[0]) : '';
						$uf          = (!empty($hUf))     ? strip_tags($hUf[0]) : '';
						$fone        = (!empty($hFone))   ? strip_tags($hFone[0]) : '';

						if($exibeSaida):
							$this->imprimeSaida($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, true);
						else:
							$this->insertAnuncio($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, true);
						endif;
						
						$contAnuncio++;
					endforeach;

					$page++;
					unlink('temp.html');
				endif;
			endwhile;

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}

	/************************************************************************************************/

	protected function getHTMLFimAnuncios(){
		try{
			$html = file_get_contents("temp.html", true);
			$arrayOutraCidade = explode("headerListing headerDistance", $html);

			if (count($arrayOutraCidade) > 1):
				return $arrayOutraCidade[0];
			else:
				$arrayAtendemOutraCidade = explode("headerListing headerPackageLocation", $html);
				if(count($arrayAtendemOutraCidade) > 1):
					return $arrayAtendemOutraCidade[0];
				else:
					return null;
				endif;
			endif;
		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
	}
}

<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time','-1');
header('Content-Type: text/html; charset=utf-8');  
	
abstract class importacao{

	protected $conexao = null;
	protected $limteAnuncio = 50;
	protected $idImportacao = 0;
	protected $contInserido = 0;
	protected $contDuplicado = 0;

	/***********************************************************************************************************************************/

	abstract protected function isPossuiPaginacao();
	abstract protected function isPadraoCorretoHTML($url);
	abstract protected function getHTMLFimAnuncios();
	abstract protected function limpaCabecalho($html);
	abstract protected function limpaRodape($html);
	abstract protected function limpaHTML();
	abstract public function parseHTML($url, $exibeSaida=false);

	/***********************************************************************************************************************************/

	public function __construct($conexao=null, $idImportacao){
		$this->setConexao($conexao);
		$this->setIdImportacao($idImportacao);
	}

	/***********************************************************************************************************************************/


	public function setConexao($conexao){
		if($conexao != null):
			$this->conexao = $conexao;
		else:
			exit();
		endif;
	}

	/***********************************************************************************************************************************/

	public function setLimiteAnuncio($limite){
		if($limite != ''):
			$this->limite= $limite;
		else:
			exit();
		endif;
	}

	public function setIdImportacao($idImportacao){
		if($idImportacao != ''):
			$this->idImportacao= $idImportacao;
		else:
			exit();
		endif;
	}

	/***********************************************************************************************************************************/

	protected function isHTMLTempGravado($urlPesquisa){

		try{
			if(file_exists("temp.html")) unlink("temp.html");

			$pagina = $this->file_get_contents_curl($urlPesquisa);
			$handle = fopen("temp.html", "w");
			fwrite($handle, $pagina);
			fclose($handle);

			return true;
		}catch(Exception $e){
			echo 'Erro ao gravar html temporário: ' . $e->getMessage();
			return false;
		}
	}

	/***********************************************************************************************************************************/

	protected function file_get_contents_curl($url) {
	      $ch = curl_init();
	      curl_setopt($ch, CURLOPT_HEADER, 0);
	      curl_setopt($ch, CURLOPT_TIMEOUT, 900); 
		  curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   
	      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	      curl_setopt($ch, CURLOPT_URL, $url);
	      $data = curl_exec($ch);
	      curl_close($ch);

	      return $data;
  	}

	/***********************************************************************************************************************************/

	protected function isAnuncioDuplicado($titulo, $telefone){
		try{

			if(!empty($titulo)):

				$where = (empty($telefone)) ? "WHERE titulo = ?" : "WHERE titulo = ? OR telefone = ? OR telefone2 = ? OR telefone3 = ? OR telefone4 = ? OR telefone5 = ? OR telefone6 = ?" ;

				$sql = "SELECT TOP 1 id FROM anuncios " . $where;
				$stm = $this->conexao->prepare($sql);

				if(empty($telefone)):
					$stm->bindValue(1, $titulo);
				else:
					$stm->bindValue(1, $titulo);
					$stm->bindValue(2, $telefone);
					$stm->bindValue(3, $telefone);
					$stm->bindValue(4, $telefone);
					$stm->bindValue(5, $telefone);
					$stm->bindValue(6, $telefone);
					$stm->bindValue(7, $telefone);
				endif;

				$stm->execute();
				$retorno = $stm->fetch(PDO::FETCH_OBJ);

				if (!empty($retorno)):
					return true;
				else:
					return false;
				endif;
			else:
				return true;
			endif;

		}catch(PDOException $e){
			echo 'Erro ao verificar duplicação: ' . $e->getMessage();
			return false;
		}
	}

	/***********************************************************************************************************************************/

	protected function insertAnuncio($titulo, $logradouro, $numero, $bairro, $cidade, $uf, $cep, $telefone, $converteTexto=false){

		try{

			$titulo = ($converteTexto) ? trim($this->converteTextoCamelCase($titulo)) : trim($titulo);

			if(!$this->isAnuncioDuplicado($titulo, $telefone)):
				$sql = "INSERT INTO importacao_temp (titulo, endereco, numero, bairro, cidade, uf, telefone, cep, id_importacao)VALUES(?,?,?,?,?,?,?,?,?)";
				$stm = $this->conexao->prepare($sql);
				$stm->bindValue(1, trim($titulo));
				$stm->bindValue(2, trim($logradouro));
				$stm->bindValue(3, trim($numero));
				$stm->bindValue(4, trim($bairro));
				$stm->bindValue(5, trim($cidade));
				$stm->bindValue(6, trim($uf));
				$stm->bindValue(7, trim($telefone));
				$stm->bindValue(8, trim($cep));
				$stm->bindValue(9, trim($this->idImportacao));
				$stm->execute();

				$this->contInserido++;
			else:
				$this->contDuplicado++;
			endif;

		}catch(PDOException $e){
			echo 'Erro ao inserir anuncios temporários: ' . $e->getMessage();
		}
	}

	/***********************************************************************************************************************************/

	protected function finalizaImportacao(){

		try{

			$sql = "UPDATE importacao_anuncios SET qtd_inserido = ?, qtd_duplicado = ?, data_execucao = ? WHERE id = ?";
			$stm = $this->conexao->prepare($sql);
			$stm->bindValue(1, $this->contInserido);
			$stm->bindValue(2, $this->contDuplicado);
			$stm->bindValue(3, date('Y-m-d'));
			$stm->bindValue(4, $this->idImportacao);
			$stm->execute();
			
			if(file_exists("temp.html")) unlink("temp.html");

			echo "<script>window.close()</script>";
		}catch(PDOException $e){
			echo 'Erro ao finalizar importação: ' . $e->getMessage();
		}
	}


	/***********************************************************************************************************************************/

  	protected function converteTextoCamelCase($nome){
		$nome = strtr(strtolower($nome),"ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß", "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ");
		$palavra=explode(" ",$nome);
		$nomeconvertido = "";

		for ($i=0; $i < count($palavra) ; $i++):
		    if ($palavra[$i] != "da" && $palavra[$i] != "de" && $palavra[$i] != "do" && $palavra[$i] != "das" && $palavra[$i] != "dos"):
		       $palavra[$i] = ucwords($palavra[$i]);
		       $primeira  = substr( $palavra[$i], 0, 1);
		       $resto  = substr( $palavra[$i], 1, 100);
		       $primeira = str_replace($primeira,strtr($primeira,"àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ","ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß"),$primeira);
		       $palavra[$i] = $primeira.$resto;
		    endif;

		    $nomeconvertido = $nomeconvertido." ".$palavra[$i];
		endfor;

		return $nomeconvertido;
	}

	/***********************************************************************************************************************************/

	protected function imprimeSaida($titulo, $endereco, $numero, $bairro, $cidade, $uf, $cep, $fone, $converteTexto=false){
    	try{
    		$titulo = ($converteTexto) ? trim($this->converteTextoCamelCase($titulo)) : trim($titulo);

			echo "TÍTULO: " . $titulo ."<br>";
			echo "RUA: " . $endereco."<br>";
			echo "NUMERO: " . $numero."<br>";
			echo "BAIRRO: " . $bairro."<br>";
			echo "CIDADE: " . $cidade."<br>";
			echo "UF: " . $uf."<br>";
			echo "CEP: " . $cep."<br>";
			echo "FONE: " . $fone."<br><br>******************<br>";

		}catch(Exception $e){
			echo 'Erro: ' . $e->getMessage();
		}
    }
}
?>
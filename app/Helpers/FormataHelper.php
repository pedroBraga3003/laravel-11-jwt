<?php

/**
 * Classe de utilidades para o sistema
 * @version 1
 * @author Pedro <pero.phnb@gmail.com>
 */

namespace App\Helpers;
class FormataHelper {

    /**
	 * Transforma o numero em float
	 * 
	 * 
	 * @param unknown $num
	 * @return number
	 */
	public static function tofloat($num){
		$dotPos = strrpos($num, '.');
		$commaPos = strrpos($num, ',');
		$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
		((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
		if (!$sep) 
		{
			return floatval(preg_replace("/[^0-9]/", "", $num));
		}
		return floatval(
				preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
				preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
		);
    }
    /**
	 * Formata um número de acordo com o padrão
	 * monetário estabelecido.
	 *
	 * @param  $number             - o numero que será formatado
	 * @param  $decimals[optional] - quantas casas decimais terá
	 * @param  $thousand[optional] - se usará separador de milhar
	 * @return string
	 * @access public
	 */
	public static function formataValor($number, $decimals = false, $dec_point = false, $thousand = false){
		// transforma o valor para float
		$number = self::tofloat($number);
		// verifica se é um número válido
		if ( $number === false ){
			return false;
        }
		// formata o número
		return (string) number_format(
				$number,
				$decimals  === false ? '2'  : $decimals,
				$dec_point === false ? ','  : $dec_point,
				$thousand  === false ? '.'  : $thousand
		);
    }
	/**
	 * Método implementado para Abreviar Nome
	 *
	 * Exemplo de : 'Pedro Henrique Novaes Braga' para 'Pedro H. N. Braga'
	 *
	 * @return boolean
	 * @access public
	 */
	public static function abreviarNome($nome){
		//nome formatado
		$nome_formatado = '';
		if(isset($nome) && $nome != ''){
			//remove excesso de  espacos
			$nome = preg_replace('/( )+/', ' ', $nome);
				
			//explodir o nome
			$arrayNome = explode(' ', $nome);
				
			if(isset($arrayNome) && count($arrayNome) > 0 ){
				//indice final do array dos nomes
				$indice_final = count($arrayNome) - 1;
				foreach ($arrayNome as $key => $item){
					if($key == '0' || $key == $indice_final){
						//Primeiro e ultimo nome completos
						$nome_formatado .= $item.' ';
					}
					else{
						//caso tenha um sobrenome complementar, exemplo : 'de', 'dos' , 'da', 'das' nao abrevia ,
						//Exemplo de nome REINALDO JOSE DOS SANTOS, ao final fica:  REINALDO J. DOS SANTOS
						if(strlen($item) > 2 && strlen($item) > 3){
							$nome_formatado .= substr($item, '0', '1').' ';
                        } else {
							$nome_formatado .= $item.' ';
                        }
					}
				}
			}
		}
		//remove excesso de  espacos
		$nome_formatado = preg_replace('/( )+/', ' ', $nome_formatado);
		return $nome_formatado;
	}
	/**
	 * formatar bytes para mostrar tamanho
	 *
	 * @param [type] $bytes
	 * @return void
	 */
	public static function formatarBytes($bytes){
		$bytes = (int) $bytes;
		$retorno  = '';
		if ($bytes >= 1000000000) {
			return round($bytes / 1000000000,2) . ' GB';
		}
		if ($bytes >= 1000000) {
			return round($bytes / 1000000,2) . ' MB';
		}
		return round($bytes / 1000,2) . ' KB';
	}
	/**
	 * Formata o telefone para mostrar na tela 
	 * 
	 * 
	 */
	public static function formataTelefone($telefone)
	{
		$formatado = (string) '';
		if ( strlen($telefone) == 10 )
		{
			$formatado .= '(' . substr($telefone, 0, 2) . ') '
					.  substr($telefone, 2, 4) . '-'
							.  substr($telefone, 6, 4);
		}
		elseif( strlen($telefone) == 11 )
		{
			$formatado .= '(' . substr($telefone, 0, 2) . ') '
					.  substr($telefone, 2, 5) . '-'
							.  substr($telefone, 7, 4);
		}
		else
			$formatado = $telefone;
	
		return $formatado;
	}

}
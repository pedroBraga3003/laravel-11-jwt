<?php
namespace App\Helpers;
/**
 * Classe para tratar pedidos
 * Exemplo - {{ \App\Helpers\PedidoHelper::fooBar() }}
 * @version 1
 * @author Pedro <pedro.phnb@gmail.com>
 */
use Illuminate\Http\Request;
use App\Mail\CancelamentoMail;
use App\Mail\AprovacaoMail;
use Mail;
class PedidoHelper {
    /**
     * Listar Status
     * 
     * Retorna um array com as poss veis op es de status para o pedido.
     * 
     * @return array
     */
    public static function listaStatus(){
        $listaStatus = [];
        $listaStatus = [
            'S' => 'Solicitado',
            'A' => 'Aprovado',
            'C' => 'Cancelado'
        ];
        
        return $listaStatus;
    }
    /**
     * Retorna a descrição completa do status baseado na sua sigla.
     * 
     * @param string $status - A sigla do status ('S', 'A', 'C').
     * @return string - A descrição completa do status.
     */
    public static function siglaStatus($status){
        $descricao_status = '';
        switch ($status) {
            case 'S':
                // Status solicitado
                $descricao_status = 'Solicitado';
                break;
            case 'A':
                // Status aprovado
                $descricao_status = 'Aprovado';
                break;
            case 'C':
                // Status cancelado
                $descricao_status = 'Cancelado';
                break;
            default:
                // Status desconhecido
                $descricao_status = 'Desconhecido';
                break;
        }
        return $descricao_status;
    }
    /**
     * Verifica se o peddido pode ser cancelado.
     * 
     * @param \App\Models\Pedido $pedido - O pedido a ser verificado.
     * 
     * @return array - Um array com as informa es de erro.
     */
    public static function verificaRegraCancelamento($pedido,$configuracao){
        $retorno = [];
        $retorno['sucesso'] = false;
        $retorno['mensagem'] = 'Pedido já cancelado.';
        $retorno['status'] = self::siglaStatus($pedido->status);
        $data_limite_cancelamento = '';
        $data_limite_cancelamento = \App\Helpers\DataHelper::subtrairDias($pedido->data_ida,$configuracao->limite_dias_cancelamento ?? 3);
        
        if($pedido->status == 'A'){
            $retorno['sucesso'] = true;
            $retorno['mensagem'] = 'Data da viagem está dentro do prazo, pode cancelar.';
            if( (\App\Helpers\DataHelper::totime($pedido->data_ida) > \App\Helpers\DataHelper::totime($data_limite_cancelamento) ) && (\App\Helpers\DataHelper::totime(date('Y-m-d')) > \App\Helpers\DataHelper::totime($data_limite_cancelamento) ) ){
                $retorno['sucesso'] = false;
                $retorno['mensagem'] = 'Não é possivel cancelar o pedido, a data limite para cancelamento era '.$data_limite_cancelamento.' e já expirou, O limite padrão é de   '.$configuracao->limite_dias_cancelamento.' dias antes da data de ida  '.\App\Helpers\DataHelper::formataData($pedido->data_ida).'.';
            }
        }
        if($pedido->status == 'S'){
            $retorno['sucesso'] = true;
            $retorno['mensagem'] = 'Pedido ainda nao foi aprovado, permite cancelar.';
            if( (\App\Helpers\DataHelper::totime($pedido->data_ida) > \App\Helpers\DataHelper::totime($data_limite_cancelamento) ) && (\App\Helpers\DataHelper::totime(date('Y-m-d')) > \App\Helpers\DataHelper::totime($data_limite_cancelamento) ) ){
                $retorno['sucesso'] = false;
                $retorno['mensagem'] = 'Não é possivel cancelar o pedido, a data limite para cancelamento era '.$data_limite_cancelamento.' e já expirou, O limite padrão é de   '.$configuracao->limite_dias_cancelamento.' dias antes da data de ida  '.\App\Helpers\DataHelper::formataData($pedido->data_ida).'.';
            }
        }
        return $retorno;
    }
    /**
     * Verifica para disparar notificação para o cliente se o pedido estiver da regra de alteração de status.
     * 
     * @param \App\Models\Pedido $pedido - O pedido a ser verificado.
     * @param string $status_alterar - O status que o pedido será alterado.
     * 
     * @return array - Um array com as informa es de erro.
     */
    public static function verificaDisparoNotificacao($pedido,$status_alterar){
        $retorno = [];
        $retorno['sucesso'] = false;
        $retorno['mensagem'] = '';
        $retorno['notificacao'] = '';
        if($pedido->status == 'S' && $status_alterar == 'A'){
            // Pedido solicitado e o status alterar para aprovado
            $retorno['sucesso'] = true;
            $retorno['mensagem'] = 'Pedido Aprovado com sucesso';
            $retorno['notificacao'] = 'A';
        }
        if( ($pedido->status == 'S' && $status_alterar == 'C') || ($pedido->status == 'A' && $status_alterar == 'C')) {
            // Pedido solicitado e o status alterar para cancelado            
            $retorno['sucesso'] = true;
            $retorno['mensagem'] = 'Pedido Cancelado com sucesso';
            $retorno['notificacao'] = 'C';
        }
        return $retorno;
    }
    /**
     * Dispara notificação para o cliente de acordo com a regra de alteração de status.
     * 
     * @param \App\Models\Pedido $pedido - O pedido a ser notificado.
     * @param string $notificacao - A notificacao a ser disparada ('A' para aprovado, 'C' para cancelado).
     * 
     * @return void
     */
    public static function disparoNotificacao($pedido,$notificacao){
        $details = [];
        $dados = [];
        $dados['id'] = $pedido->id;
        $dados['user_id'] = $pedido->user_id;
        $dados['nome_usuario'] = $pedido->nome_usuario;
        $dados['codigo_pedido'] = $pedido->codigo_pedido;
        $dados['origem'] = $pedido->origem;
        $dados['destino'] = $pedido->destino;
        $dados['data_ida'] = \App\Helpers\DataHelper::formataData($pedido->data_ida);
        $dados['data_volta'] = \App\Helpers\DataHelper::formataData($pedido->data_volta);
        $dados['status'] = $pedido->status;
        $dados['created_at'] = $pedido->created_at->format('d/m/Y');
        $dados['updated_at'] = $pedido->updated_at->format('d/m/Y');
        $dados['tipo_usuario'] = $pedido->tipo_usuario;
        $dados['nome_cliente'] = $pedido->nome_cliente;
        $dados['email_cliente'] = $pedido->email_cliente;

        if($notificacao['notificacao'] ==  'A'){
            $details['title'] = 'Email de cancelamento';
            $details['body'] = 'Cancelado';
            $details['dados'] = $dados;
            // $retorno = \Mail::to($dados['email_cliente'])->send(new \App\Mail\CancelamentoMail($details));
            $retorno = Mail::to($dados['email_cliente'])->send(new \App\Mail\AprovacaoMail($details));
        }
        if($notificacao['notificacao'] == 'C'){
            $details['title'] = 'Email de cancelamento';
            $details['body'] = 'Cancelado';
            $details['dados'] = $dados;
            // $retorno = \Mail::to($dados['email_cliente'])->send(new \App\Mail\CancelamentoMail($details));
            $retorno = Mail::to($dados['email_cliente'])->send(new \App\Mail\CancelamentoMail($details));
        }
        return true;
    }
}

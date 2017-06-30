<?php
	require_once('Controller.php');
	require_once('Operacao.php');
	class OperacaoController extends Controller{
		function __construct(){
			parent::__construct();
		}

		protected function check_if_exists($app,$id){
			if(Operacao::get_one($app,$id)==null ){
				return false;
			}
			return true;
		}
		/**
		 * REsponsavel por atender requisicoes GET na url /Operacoes/. Retorna todas as operacoes cadastradas
		 * 
		 * O conteudo da resposta eh em JSON
		 * 
		 *
		 * @param      Silex\Application  $app    The application
		 * @param      string             $id     The identifier
		 *
		 * @return     Response             Response com conteudo no formato JSON
		 */
		function get_all(Silex\Application $app){
			$operation_array = Operacao::get_all($app);
			if($operation_array == null){
				return $app->json(array('msg' => "Nenhuma Operacao Encontrada"),404);
			}else{
				$data = array();
				foreach ($operation_array as $operation_obj) {
					$data[]=array(
						'id' => $operation_obj->id,
						'qnt' => $operation_obj->qnt,
						'preco' => $operation_obj->price,
						'tipo_transacao' => $operation_obj->transactype,
						'status' => $operation_obj->status,
						'cod_produto' => $operation_obj->product->codprod,
						'tipo_produto' => $operation_obj->product->typeprod,
						'name_produto' => $operation_obj->product->name
					);
				}
				return $app->json(array('operacoes' => $data),200);
			}
		}

		/**
		 * REsponsavel por atender requisicoes GET na url /operacoes/{id}. Retorna uma unica operacao com o id especificado
		 * 
		 * O conteudo da resposta eh em JSON
		 * 
		 *
		 * @param      Silex\Application  $app    The application
		 * @param      string             $id     The identifier
		 *
		 * @return     Response             Response com conteudo no formato JSON
		 */
		function get_one(Silex\Application $app, $id){
			$operation_obj = Operacao::get_one($app,$id);
			if($operation_obj == null){
				return $app->json(array('msg' => "Operacao com id ".$id." nao encontrada"),404);
			}else{
				$data=array(
					'id' => $operation_obj->id,
						'qnt' => $operation_obj->qnt,
						'preco' => $operation_obj->price,
						'tipo_transacao' => $operation_obj->transactype,
						'status' => $operation_obj->status,
						'cod_produto' => $operation_obj->product->codprod,
						'tipo_produto' => $operation_obj->product->typeprod,
						'name_produto' => $operation_obj->product->name
				);
				return $app->json(array('operacao' => $data),200);
			}


		}

		/**
		 * REsponsavel por atender requisicoes POST na url /operacoes/. Insere uma operacao no banco
		 * 
		 * O conteudo da resposta eh em JSON
		 * 
		 *
		 * @param      Silex\Application  $app    The application
		 * @param      string             $id     The identifier
		 *
		 * @return     Response             Response com conteudo no formato JSON
		 */

		function post(Silex\Application $app, Symfony\Component\HttpFoundation\Request $request){
			
			$data = json_decode($request->getContent(),false);

			$operation_obj = new Operacao();

			$operation_obj->fill(
			-1,$data->qnt,$data->preco,$data->tipo_transacao,$data->status,$data->cod_produto,$data->tipo_produto,$data->name_produto);
			$res = $operation_obj->insert($app);
			switch ($res) {
				case DatabaseManipulation::INSERTION_SUCCESS:
					return $app->json(array('msg' => "Operacao Inserida."),200);
					break;
				case DatabaseManipulation::INSERTION_ERROR_DUPLICATED_ENTRY:

					return $app->json(array('msg' => "Erro ao inserir Operacao. Valor Duplicado."),403);
					break;
				case DatabaseManipulation::INSERTION_ERROR_INVALID_VALUE:

					return $app->json(array('msg' => "Erro ao inserir Operacao. Algum campo contem vlaor invalido."),403);
					break;
				default:

					return $app->json(array('msg' => "Erro desconhecido ao inserir Operacao"),400);
					break;
			}
			
		}
	}

?>
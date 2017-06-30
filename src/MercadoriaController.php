<?php
	require_once('../vendor/autoload.php');
	require_once("Controller.php");
	require_once("Mercadoria.php");

	/**
	 * Classe responsavel por manipular as "Requests" relacionados a Mercadoria. 
	 * 
	 * Deve fazer o parse dos dados vindo da requisicao HTTP, chamar as funcoes internas para atende-las, e construir o response
	 */
	class MercadoriaController extends Controller{
		function __construct(){
			parent::__construct();
		}

		/**
		 * Checa se Mercadoria com o codigo $id existe no banco
		 *
		 * @param      Silex\Application   $app    The application
		 * @param      int   $id     The identifier
		 *
		 * @return     boolean  true se existe, false caso contrario
		 */
		protected function check_if_exists($app,$id){
			if(Mercadoria::get_one($app,$id)==null ){
				return false;
			}
			return true;
		}

		/**
		 * REsponsavel por atender requisicoes GET na url /mercadorias/{id}. Retorna uma unica mercadoria com o id especificado
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
			$product_obj = Mercadoria::get_one($app,$id);
			if($product_obj == null){
				return $app->json(array('msg' => "Mercadoria com prodcode ".$id." não encontrada"),404);
			}else{
				$data=array(
					'cod' => $product_obj->codprod,
					'type' => $product_obj->typeprod,
					'name' => $product_obj->name
				);
				return $app->json(array('mercadoria' => $data),200);
			}


		}

		/**
		 * REsponsavel por atender requisicoes GET na url /mercadorias/. Retorna todas as mercadorias cadastradas
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
			$product_array = Mercadoria::get_all($app);
			if($product_array == null){
				return $app->json(array('msg' => "Nenhuma Mercadoria Encontrada"),404);
			}else{
				$data = array();
				foreach ($product_array as $product_obj) {
					$data[]=array(
						'cod' => $product_obj->codprod,
						'type' => $product_obj->typeprod,
						'name' => $product_obj->name
					);
				}
				return $app->json(array('mercadorias' => $data),200);
			}
		}

		/**
		 * REsponsavel por atender requisicoes POST na url /mercadorias/. Insere uma mercadoria no banco
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
			
			$product_obj = new Mercadoria();
			$product_obj->fill($data->cod,$data->type,$data->name);

			$res = $product_obj->insert($app);

			

			switch ($res) {
				case DatabaseManipulation::INSERTION_SUCCESS:
					return $app->json(array('msg' => "Mercadoria Inserida."),200);
					break;
				case DatabaseManipulation::INSERTION_ERROR_DUPLICATED_ENTRY:

					return $app->json(array('msg' => "Erro ao inserir mercadoria. Valor Duplicado."),403);
					break;
				case DatabaseManipulation::INSERTION_ERROR_INVALID_VALUE:

					return $app->json(array('msg' => "Erro ao inserir mercadoria. Algum campo contem vlaor invalido."),403);
					break;
				default:

					return $app->json(array('msg' => "Erro desconhecido ao inserir mercadoria"),400);
					break;
			}
		}
	}
?>
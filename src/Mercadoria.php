<?php
	require_once('DatabaseManipulation.php');

	/**
	 * Classe responsavel por abstrair acesso a tabela de Mercadorias
	 */
	class Mercadoria extends DatabaseManipulation{
		public $codprod; ///<Codigo do produto
		public $typeprod; ///<Tipo do produto
		public $name; ///<nome do produto

		
		public function __construct(){
			
			$this->codprod = -1;
			$this->typeprod = "";
			$this->name = "";
		}

		/**
		 * Preenche o objeto atual com os valores respectivos. 
		 *
		 * @param      int  $codprod   Codigo
		 * @param      string  $typeprod  Tipo
		 * @param      string  $name      Nome
		 */
		public function fill($codprod,$typeprod,$name){
			$this->codprod = $codprod;
			$this->typeprod = $typeprod;
			$this->name = $name;
		}

		/**
		 * Busca no banco a Mercadoria com o codigo $id.
		 *
		 * @param      Silex\Application  $app    The application
		 * @param      int             $id     The identifier
		 *
		 * @return     Mercadoria       Objeto Mercadoria com as informacoes se a mercadoria existe, null caso contrario
		 */
		static function get_one(Silex\Application $app, $id){
			$sql = 'SELECT  
							prodcode,
							typeprod,
							name
						FROM product_table
						WHERE 
							prodcode = :codprodPH
			;';

			$statement = $app['pdo']->prepare($sql);
			
			$statement->bindParam(':codprodPH',$id);

			if(!$statement->execute()){
				return null;
			}else{
				$res = $statement->fetchAll();
				if(count($res)==0){
					return null;
				}
				$obj = new Mercadoria();
				$obj->fill($res[0]['prodcode'],$res[0]['typeprod'],$res[0]['name']);
				return $obj;
			}
		}

		/**
		 * Retorna um vetor de objetos do tipo Mercadoria com todas as mercadorias cadastradas
		 *
		 * @param      Silex\Application  $app    The application
		 *
		 * @return     array              Array de objetos Mercadoria se existe alguma mercadoria cadastrada. null caso contrario
		 */
		static function get_all(Silex\Application $app){
			$sql = 'SELECT  
							prodcode,
							typeprod,
							name
						FROM product_table
			;';

			$statement = $app['pdo']->prepare($sql);
			

			if(!$statement->execute()){
				return null;
			}else{
				$res = $statement->fetchAll();
				
				$return_array = array();

				if(count($res)==0){ //se nao encontrou mercadoria, retorna nulo
					return null;
				}
				foreach ($res as $prod ) {
					$aux = new Mercadoria();
					$aux->fill($prod['prodcode'],$prod['typeprod'],$prod['name']);
					$return_array[]=$aux;
				}

				return $return_array;
			}
		}

		/**
		 * Insere o objeto atual no banco de dados
		 *
		 * @param      Silex\Application  $app    The application
		 *
		 * @return     integer             Algum dos codigos de erro especificados na classe DatabaseManipulation
		 */
		function insert(Silex\Application $app){			
			$sql = 'INSERT INTO product_table 
								(prodcode,
								typeprod,
								name
							)
						VALUES (:codprodPH,
								:typeprodPH,
								:namePH								
						)
			;';

			$statement = $app['pdo']->prepare($sql);
			
			//Seta os valores a serem inseridos, pegando do objeto atual
			//A classe PDO do Postgre esta sendo utilizada para setar os parametros, dando uma protecao basica contra SQL injection
			$statement->bindParam(':codprodPH',$this->codprod);
			$statement->bindParam(':typeprodPH',$this->typeprod);
			$statement->bindParam(':namePH',$this->name);

			if($statement->execute()){
				return DatabaseManipulation::INSERTION_SUCCESS;
			}else{
				$error_info = $statement->errorInfo();
				error_log($error_info[0]);
				error_log($error_info[1]);
				error_log($error_info[2]);
				if(intval($error_info[0])==23505){ //testa especificamente se o tipo de erro foi uma violacao de restricao UNIQUE
					return DatabaseManipulation::INSERTION_ERROR_DUPLICATED_ENTRY;
				}else if (substr($error_info[0],0,2)=='23'){ //testa se foi algum outro tipo de erro de classe 23, o que indicaria erro nos dados
					return DatabaseManipulation::INSERTION_ERROR_INVALID_VALUE;
				}else{
					return DatabaseManipulation::INSERTION_ERROR_UNKNOWN;
				}
			}

			

			

		}

		/**
		 * Atualiza o objeto atual no banco de dados
		 *
		 * @param      Silex\Application  $app    The application
		 *
		 * @return     int             Algum dos codigos de erro especificados na classe DatabaseManipulation
		 */
		function update(Silex\Application $app){
			
		

			$sql = "UPDATE product_table 
						SET 
							typeprod = :typeprodPH,
							name = :namePH
						WHERE
							prodcode = :codprodPH
			;";

			//Seta os valores a serem inseridos, pegando do objeto atual
			//A classe PDO do Postgre esta sendo utilizada para setar os parametros, dando uma protecao basica contra SQL injection
			$statement = $app['pdo']->prepare($sql);
			$statement->bindParam(':codprodPH',$this->codprod);
			$statement->bindParam(':typeprodPH',$this->typeprod);
			$statement->bindParam(':namePH',$this->name);


			if($statement->execute()){
				return DatabaseManipulation::INSERTION_SUCCESS;
			}else{
				$error_info = $statement->errorInfo();
				error_log($error_info[0]);
				error_log($error_info[1]);
				error_log($error_info[2]);
				if(intval($error_info[0])==23505){//testa especificamente se o tipo de erro foi uma violacao de restricao UNIQUE
					return DatabaseManipulation::INSERTION_ERROR_DUPLICATED_ENTRY;
				}else if (substr($error_info[0],0,2)=='23'){ //testa se foi algum outro tipo de erro de classe 23, o que indicaria erro nos 
					return DatabaseManipulation::INSERTION_ERROR_INVALID_VALUE;
				}else{
					return DatabaseManipulation::INSERTION_ERROR_UNKNOWN;
				}
			}
		}
	
	}

?>
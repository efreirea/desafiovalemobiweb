<?php
	require_once('DatabaseManipulation.php');
	require_once('Mercadoria.php');
	class Operacao extends DatabaseManipulation{

		public $id; ///<id da operacao
		public $qnt; ///<Quantidade
		public $price; ///<Preco total da operacao
		public $transactype;///<Tipo de operacao
		public $product=false;
		
		const STATUS_UNDEFINED= 0;
		const STATUS_OPEN= 1;
		const SATATUS_CONFIRMED=2;
		/**
		 * Inicialmente, pensou-se em implementar a interface do usuario de maneira que ele poderia salvar operacoes, mas sem
		 * confirma-las. No entanto, julgou-se que isso sairia do escopo do desafio, entao foi descartado.
		 */
		public $status;///<Nao utilizado

		function __construct(){
			$this->id = -1;
			$this->qnt = -1;
			$this->price = -1;
			$this->transaction_type = self::STATUS_UNDEFINED;

			$this->table_name = 'operation_table';

		}

		/**
		 * Preenche os campos do objeto atual com os valores dados. Um objeto do tipo MErcadoria e criado
		 *
		 * @param      int  $id            ID da operacao
		 * @param      int  $qnt           quantidae de produtos
		 * @param      real  $price         Preco total da operacao
		 * @param      char  $transactype   Tipo de trnsacao
		 * @param      int  $status        Status
		 * @param      int  $product_code  codigo do produto
		 * @param      string  $product_type  tipo do produto
		 * @param      string  $product_name  nome do produto
		 */
		function fill($id,$qnt,$price,$transactype,$status,$product_code,$product_type,$product_name){
			$this->id = $id;
			$this->qnt = $qnt;
			$this->price = $price;
			$this->transactype = $transactype;
			$this->status=$status;

			$this->product = new Mercadoria();
			$this->product->fill($product_code,$product_type,$product_name);
		}
		/**
		 * Retorna um vetor todas as operacoes
		 *
		 * @param      Silex\Application  $app    The application
		 *
		 * @return     array              Array de objetos do tipo Operacao, ou nulo se nao existir nenhuma operacao cadastrada
		 */
		static function get_all(Silex\Application $app){
			$sql = 'SELECT  
							O.id,
							O.prodcode,
							O.qnt,
							O.price,
							O.transactype,
							O.status,
							P.typeprod,
							P.name
						FROM operation_table O
							JOIN product_table P ON O.prodcode=P.prodcode
			;';

			$statement = $app['pdo']->prepare($sql);
			

			if(!$statement->execute()){
				return null;
			}else{
				$res = $statement->fetchAll();
				
				$return_array = array();

				if(count($res)==0){
					return null;
				}
				
				foreach ($res as $operation ) {

					$aux = new Operacao();
					$aux->fill($operation['id'],$operation['qnt'],$operation['price'],$operation['transactype'],$operation['status'],$operation['prodcode'],$operation['typeprod'],$operation['name']);
					$return_array[]=$aux;
				}

				return $return_array;
			}
		}

		/**
		 * REtorna uma operacao com o id $id.
		 *
		 * @param      Silex\Application  $app    The application
		 * @param      <type>             $id     The identifier
		 *
		 * @return     Operacao           One.
		 */
		static function get_one(Silex\Application $app, $id){
			//REaliza uma query dando join para pegar tambem os dados de produto
			$sql = 'SELECT  
							O.id,
							O.prodcode,
							O.qnt,
							O.price,
							O.transactype,
							O.status,
							P.typeprod,
							P.name
						FROM operation_table O
							JOIN product_table P ON O.prodcode=P.prodcode
						WHERE
							O.id = :idPH
			;';

			$statement = $app['pdo']->prepare($sql);
			
			$statement->bindParam(':idPH',$id);

			if(!$statement->execute()){
				return null;
			}else{
				$res = $statement->fetchAll();
				if(count($res)==0){
					return null;
				}
				$obj = new Operacao();
				$obj->fill($res[0]['id'],$res[0]['qnt'],$res[0]['price'],$res[0]['transactype'],$res[0]['status'],$res[0]['prodcode'],$res[0]['typeprod'],$res[0]['name']);
				return $obj;
			}
		}

		/**
		 * Insere no banco de dados a operacao representada pelo objeto atual.
		 *
		 * @param      Silex\Application  $app    The application
		 *
		 * @return     int             Um dos codigos de erro especificados na classe DatabaseManipulation
		 */
		function insert(Silex\Application $app){
			
			$product_code = $this->product->codprod;

			//Se o produto com o codigo espeficado ainda nao existe, entao insere a mercadoria.
			//Note que se ja existe uma mercadoria com o codigo, ela nao eh atualizada.
			// Em outras palavras, nao importa o conteudo de $this->product->typeprod e $this->product->name
			if(Mercadoria::get_one($app,$product_code)==null ){
				$this->product->insert($app);
			}

			//Se o objeto atual nao possui id definido, entao utiliza-se a sequencia operation_ID_Sequence para gerar o ID
			//Nesse sistema, eh o caso padrao
			if($this->id == -1){
				$sql = 'INSERT INTO operation_table 
									(id,
									prodcode,
									qnt,
									price,
									transactype,
									status)
							VALUES (nextval(\'operation_ID_Sequence\'),
									:prodcode_ph,
									:qnt_ph,
									:price_ph,
									:transactype_ph,
									:status_ph
							)
				;';
				
			}else{ //caso $id ja esteja setado, entao ele eh utilizado
				$sql = 'INSERT INTO operation_table 
								(id,
								prodcode,
								qnt,
								price,
								transactype,
								status)
						VALUES (:id_ph,
								:prodcode_ph,
								:qnt_ph,
								:price_ph,
								:transactype_ph,
								:status_ph
						)
			;';

			}


			$statement = $app['pdo']->prepare($sql);
			
			if($this->id > -1) $statement->bindparam(':id_ph',$this->id); //se $id esta setado, entao utliza ele na query
			$statement->bindparam(':prodcode_ph',$product_code);
			$statement->bindparam(':qnt_ph',$this->qnt);
			$statement->bindparam(':price_ph',$this->price);
			$statement->bindparam(':transactype_ph',$this->transactype);
			$statement->bindparam(':status_ph',$this->status);


			if($statement->execute()){
				return DatabaseManipulation::INSERTION_SUCCESS;
			}else{
				$error_info = $statement->errorInfo();
				error_log($error_info[0]);
				error_log($error_info[1]);
				error_log($error_info[2]);
				if(intval($error_info[0])==23505){ //Testa para o erro especifico de violacao de UNIQUE
					return DatabaseManipulation::INSERTION_ERROR_DUPLICATED_ENTRY;
				}else if (substr($error_info[0],0,2)=='23'){ // testa para a classe 23, que sao erros de dados invalidos
					return DatabaseManipulation::INSERTION_ERROR_INVALID_VALUE;
				}else{
					return DatabaseManipulation::INSERTION_ERROR_UNKNOWN;
				}
			}

		}
		/**
		 * Atualiza a operacao atual. Nao eh utilizado efetvamente no sistema
		 *
		 * @param      Silex\Application  $app    The application
		 *
		 * @return     int           Um dos codigos de erro especificados na classe DatabaseManipulation
		 */
		function update(Silex\Application $app){
			
			$product_code = $this->product->codprod;

			//SQL para atualizacao da tabela operation_table

			//Note que os dados de mercadoria nao sao atualizados e que a mercadoria deve existir.
			// Em outras palavras, nao importa o conteudo de $this->product->typeprod e $this->product->name

			$sql = "UPDATE operation_table 
						SET 
							prodcode = :prodcode_ph,
							qnt = :qnt_ph,
							price = :price_ph,
							transactype = :transactype_ph,
							status = :status_ph
						WHERE
							id = :id_ph
			;";
			

			$statement = $app['pdo']->prepare($sql);
			$statement->bindparam(':id_ph',$this->id);
			$statement->bindparam(':prodcode_ph',$product_code);
			$statement->bindparam(':qnt_ph',$this->qnt);
			$statement->bindparam(':price_ph',$this->price);
			$statement->bindparam(':transactype_ph',$this->transactype);
			$statement->bindparam(':status_ph',$this->status);

			if($statement->execute()){
				return DatabaseManipulation::INSERTION_SUCCESS;
			}else{
				$error_info = $statement->errorInfo();
				error_log($error_info[0]);
				error_log($error_info[1]);
				error_log($error_info[2]);
				if(intval($error_info[0])==23505){
					return DatabaseManipulation::INSERTION_ERROR_DUPLICATED_ENTRY;
				}else if (substr($error_info[0],0,2)=='23'){
					return DatabaseManipulation::INSERTION_ERROR_INVALID_VALUE;
				}else{
					return DatabaseManipulation::INSERTION_ERROR_UNKNOWN;
				}
			}
		}
		
	}

?>
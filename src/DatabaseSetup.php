<?php
	/**
	 * Realiza operacoes de criacao e remocao de tabelas e sequencias do banco de dados
	 */
	class DatabaseSetup{
		protected static $table_names;
		protected static $table_columns;

		/**
		 * inicializa alguns valores estaticos que podem ser uteis. 
		 */
		public static function init(){
			self::$table_names = array(
				'operat'=> 'operation_table',
				'mercad'=> 'product_table'
			);

			self::$table_columns = array(
				'operat'=> array(
					'id',
					'prodcode',
					'qnt',
					'price',
					'transactype',
					'status'
				),
				'mercad'=>array(
					'prodcode',
					'typeprod',
					'name'
				)

			);
		}
		/**
		 * Metodo nao utilizado. Foi feito na tentativa de deixar generico a criacao de tabelas, mas acabou ficando obsoleto.
		 *
		 * @param      string   $table_slug  The table slug
		 *
		 * @return     array|bool  Array com as colunas, ou false.
		 */
		function get_columns($table_slug){
			if ($table_slug == 'operat' || $table_slug=='mercad'){
				return self::$table_columns[$table_slug];
			}else{
				return false;
			}
		}

		/**
		 * Deleta todas as tabelas e seqeuncia e as cria novamente
		 *
		 * @param      <type>  $app    The application
		 */
		static function reset_database($app){
			DatabaseSetup::drop_tables($app);
			DatabaseSetup::create_tables($app);
		}

		static function drop_tables($app){
			$pdo = $app['pdo'];

			$sql = 'DROP TABLE IF EXISTS operation_table;';	

			$statement = $pdo->prepare($sql);
			
			if(!$statement->execute()){
				error_log("FATAL_ERROR: Error dropping operat table.");
				error_log($statement->errorCode());
				error_log($statement->errorInfo()[0]);
				error_log($statement->errorInfo()[2]);
			}

			$sql = "DROP TABLE IF EXISTS product_table;";

			$statement = $pdo->prepare($sql);
			

			if(!$statement->execute()){
				error_log("FATAL_ERROR: Error dropping product table.");
				error_log($statement->errorCode());
				error_log($statement->errorInfo()[0]);
				error_log($statement->errorInfo()[2]);
			}


			$sql = "DROP SEQUENCE IF EXISTS operation_ID_Sequence;";

			$statement = $pdo->prepare($sql);

			if(!$statement->execute()){
				error_log("FATAL_ERROR: Error dropping sequence.");
				error_log($statement->errorCode());
				error_log($statement->errorInfo()[0]);
				error_log($statement->errorInfo()[2]);
			}
		}

		static function create_tables($app){
			$pdo = $app['pdo'];

			$sql = 'CREATE TABLE product_table(
				prodcode bigint,
				typeprod VARCHAR(100),
				name VARCHAR(100) NOT NULL,
				PRIMARY KEY(prodcode)

			);';	

			$statement = $pdo->prepare($sql);
			
			if(!$statement->execute()){
				error_log("FATAL_ERROR: Error creating mercad table.");
				error_log($statement->errorCode());
				error_log($statement->errorInfo()[0]);
				error_log($statement->errorInfo()[2]);
			}

			$sql = "CREATE TABLE operation_table(
				id bigint,
				prodcode bigint NOT NULL,
				qnt bigint DEFAULT 0,
				price money,
				transactype CHAR(1) CHECK (transactype IN ('C','V')),
				status smallint CHECK (status IN (0,1,2)) DEFAULT 0,
				PRIMARY KEY (id),
				FOREIGN KEY (prodcode) 
					REFERENCES product_table


			); ";

			$statement = $pdo->prepare($sql);
			

			if(!$statement->execute()){
				error_log("FATAL_ERROR: Error creating operat table.");
				error_log($statement->errorCode());
				error_log($statement->errorInfo()[0]);
				error_log($statement->errorInfo()[2]);
			}


			$sql = "CREATE SEQUENCE operation_ID_Sequence;";

			$statement = $pdo->prepare($sql);

			if(!$statement->execute()){
				error_log("FATAL_ERROR: Error creating sequence.");
				error_log($statement->errorCode());
				error_log($statement->errorInfo()[0]);
				error_log($statement->errorInfo()[2]);
			}
		}
	}
	DatabaseSetup::init();
?>
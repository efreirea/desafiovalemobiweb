<?php
	require_once('../vendor/autoload.php');

	/**
	 * Classe que define uma "interface" para classes que manipulam banco. Tambem define constantes de Erro
	 */
	abstract class DatabaseManipulation{
		protected $table_name=false; //nao utilizado
		protected $id_col='id'; //nao utilizado

		
		
		const INSERTION_SUCCESS = 1;

		const INSERTION_ERROR_UNKNOWN = -1;
		const INSERTION_ERROR_DUPLICATED_ENTRY = 0;
		const INSERTION_ERROR_INVALID_VALUE = 2;

		abstract public function insert(Silex\Application $app);
		abstract public function update(Silex\Application $app);
	}

?>
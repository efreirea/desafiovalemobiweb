<?php

require('../vendor/autoload.php');
require_once('../src/DatabaseSetup.php');
require_once('../src/MercadoriaController.php');
require_once('../src/OperacaoController.php');

// Foi utilizado o framework Silex para gerenciar as routes e o PDO do PostgreSQL
$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// PAGINAS HTML
// Routes para as paginas HTML com a interface do usuario

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$app->get('/adicionarOperacao', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  $prods = Mercadoria::get_all($app);
  return $app['twig']->render('adicionarOp.twig',array(
    'products' => $prods
  ));
});

$app->get('/listar', function(Silex\Application $app) {


  $app['monolog']->addDebug('logging output.');

  
  $operations = Operacao::get_all($app);

  return $app['twig']->render('listar.twig',array(
    'operations' =>$operations
  ));
});


$dbopts = parse_url(getenv('DATABASE_URL'));
$app->register(new Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider('pdo'),
               array(
                'pdo.server' => array(
                   'driver'   => 'pgsql',
                   'user' => $dbopts["user"],
                   'password' => $dbopts["pass"],
                   'host' => $dbopts["host"],
                   'port' => $dbopts["port"],
                   'dbname' => ltrim($dbopts["path"],'/')
                   )
               )
);
// Database setup para facilitar
// routes para as funcionalidades de reset do DB
 

$app->get('/db/setup/',function() use($app){
	DatabaseSetup::create_tables($app);

	return "Database Setup!";

});

$app->get('/db/reset/',function() use($app){
  DatabaseSetup::reset_database($app);

  return "Database Reset!";

});

//MERCADORIA
//Routes para a "RESTFUL-ish" API para mercadorias

$app->get('/api/mercadorias/',function(Silex\Application $app){
	$obj = new MercadoriaController();
	return $obj->get_all($app);
});
$app->post('/api/mercadorias/',function(Silex\Application $app, Symfony\Component\HttpFoundation\Request $request) {
	$obj = new MercadoriaController();
	return $obj->post($app,$request);
});

$app->get('/api/mercadorias/{id}',function(Silex\Application $app,$id){
	$obj = new MercadoriaController();
	return $obj->get_one($app,$id);
});

//Operacao

//Routes para a "RESTFUL-ish" API para operacoes

$app->get('/api/operacoes/',function(Silex\Application $app){
	$obj = new OperacaoController();
	return $obj->get_all($app);
});

$app->post('/api/operacoes/',function(Silex\Application $app, Symfony\Component\HttpFoundation\Request $request){
	$obj = new OperacaoController();
	return $obj->post($app,$request);

});

$app->get('/api/operacoes/{id}',function(Silex\Application $app,$id){
	$obj = new OperacaoController();
	return $obj->get_one($app,$id);
});



$app->run();

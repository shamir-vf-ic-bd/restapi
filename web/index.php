<?php

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Our web handlers

$app->get('/greetings', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  
  $q=$_GET['q'];
  $arr = explode('!', $q);
  
  $ans="";
  if($arr[0]=="Hi")
  {
	$ans="I am fine.How are you?";
  }
  else if($arr[0]=="Hello")
  {
	$ans="My name is Shamir.";
  }
  else if($arr[0]=="Good morning" || $arr[0]=="Good evening" || $arr[0]=="Good night")
  {
	$ans=$arr[0]."!I am Shamir! I am also pleased to meet you!";
  }
  else
  {
	$ans="Sorry, I don't understand your question.";
  }
  
  $ans="Hello, Kitty!".$ans;
  
  $myarr = array(
  'answer'  => $ans
  );
  
  $js=json_encode($myarr);
  
  return $js;
  
});




$app->get('/qa', function() use($app) {
  $app['monolog']->addDebug('logging output.');

	return "abc";

});




$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/../views',
));

$app->get('/twig/{name}', function ($name) use ($app) {
    return $app['twig']->render('index.twig', array(
        'name' => $name,
    ));
});

$app->run();

?>

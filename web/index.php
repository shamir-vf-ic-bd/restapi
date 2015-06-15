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




$app->get('/weather', function() use($app) {
  $app['monolog']->addDebug('logging output.');

	$q=$_GET['q'];
  
	$arr = explode('<', $q);
	if(isset($arr[1]))
	{	
		$arr1 = explode('>', $arr[1]);
		//echo $arr1[0];
	}
	else return "Please Insert queston in appropiate form. Give city name inside < >. Sample question: What is today's humidity in \<Dhaka\>?";
	
	$temp="not found";
	$humidity="not found";
	$rain="No";
	$cloud="No";
	$clear="No";
	
	$response = file_get_contents('http://api.openweathermap.org/data/2.5/weather?q='.$arr1[0]);
	
	
	$json = json_decode($response, true);

	if(isset($json['main']['temp']))
	{
		$temp=$json['main']['temp'];
	
	}
	
	if(isset($json['main']['humidity']))
	{
		$humidity=$json['main']['humidity'];
	
	}
	
	if(isset($json['rain']))
	{
		$rain="Yes";
	
	}
	
	if(isset($json['clouds']))
	{
		$cloud="Yes";
	
	}
	
	if($rain=="No" && $cloud=="No") $clear="Yes";
	
	$ans="";
	
	if(strpos($q,'temperature') !== false || strpos($q,'Temperature') !== false) $ans=$temp."K";
	else if(strpos($q,'humidity') !== false || strpos($q,'Humidity') !== false) $ans=$humidity;
	else if(strpos($q,'Rain') !== false || strpos($q,'rain') !== false) $ans=$rain;
	else if(strpos($q,'Clouds') !== false || strpos($q,'clouds') !== false) $ans=$cloud;
	else if(strpos($q,'Clear') !== false || strpos($q,'clear') !== false) $ans=$clear;
	else $ans="Don't know.";
	
	
	$myarr = array(
	  'answer'  => $ans
	  );
  
	$js=json_encode($myarr);
  
	return $js;

});


$app->get('/qa', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  
  $q=$_GET['q'];
  
  $spar=file_get_contents("http://quepy.machinalis.com/engine/get_query?question=".urlencode($q)."?");
		$decode_spar=json_decode($spar,false);
		
		 $data= array(
                'debug'=> 'on',
                'timeout'=> '3000',
                'query'=> urlencode($decode_spar->queries[0]->query),
                'default-graph-uri'=> 'http://dbpedia.org',
                'format'=> 'application/sparql-results+json'
            );
			$url='http://dbpedia.org/sparql/';
			$fields_string='';
			foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
					rtrim($fields_string, '&');
			//$context = stream_context_create (array ( 'http' => $data ));
			$ch = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($data));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

			//execute post
			$result = curl_exec($ch);
			
		return $result;

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

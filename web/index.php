<?php

require('../vendor/autoload.php');
header("Content-Type:application/json");

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
  
  $spar=file_get_contents("http://quepy.machinalis.com/engine/get_query?question=".urlencode($q));
		$decode_spar=json_decode($spar,false);
		
		 $data= array(
                'debug'=> 'on',
                'timeout'=> '3000',
                'query'=> urlencode($decode_spar->queries[0]->query),
                'default-graph-uri'=> '',
                'format'=> 'application/sparql-results+json'
            );
			$url='http://dbpedia.org/sparql/';
			$fields_string='';
			foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
					rtrim($fields_string, '&');
			//$context = stream_context_create (array ( 'http' => $data ));
			
	
			
			
			$ch = curl_init();

			//set the url, number of POST vars, POST data
			//$ch,curlopt_returntransfer,true
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($data));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

			//execute post
			$result = curl_exec($ch);
		
			echo "result found from: DBpedia.";
			echo "<br>";
			
			echo $result;
		//$json = json_decode($result, true);
			
			
			$string=$q;
		
			  $stopWords = array('i','a','about','an','and','are','as','at','be','by','com','de','en','for','from','how','in','is','it','la','of','on','or','that','the','this','to','was','what','when','where','who','will','with','und','the','www');
		   
			  $string = preg_replace('/\s\s+/i', '', $string);
			  $string = trim($string);
			  $string = preg_replace('/[^a-zA-Z0-9 -]/', '', $string);
			  $string = strtolower($string);
		   
			  preg_match_all('/\b.*?\b/i', $string, $matchWords);
			  $matchWords = $matchWords[0];
			  
			  foreach ( $matchWords as $key=>$item ) {
				  if ( $item == '' || in_array(strtolower($item), $stopWords) || strlen($item) <= 3 ) {
					  unset($matchWords[$key]);
				  }
			  }   
			  $wordCountArr = array();
			  if ( is_array($matchWords) ) 
			  {
				  foreach ( $matchWords as $key => $val ) 
				  {
					  $val = strtolower($val);
					  if ( isset($wordCountArr[$val]) ) 
					  {
						  $wordCountArr[$val]++;
					  } else 
					  {
						  $wordCountArr[$val] = 1;
					  }
				  }
			  }
			  $words= implode(',', array_keys($wordCountArr));
			  $words = str_replace(',', ' ', $words);

			  //return $words;
			
			  $textapi = new AYLIEN\TextAPI("76ceedda", "ff7b37e96e63aa6d0a97a6465c949c52");
			$url='https://en.wikipedia.org/wiki/'."Putin";
			//echo $url;
			$summary= $textapi->summarize(array("url" => $url));
			//echo $summary->sentences[0].$summary->sentences[1].$summary->sentences[2];
			$ans="";
			if($summary->text == null) {
				$ans="Your majesty! Jon Snow knows nothing! So do I! Your grace, I searched a lot. But could not find a good free API. But I can manage Aylien API. By which I can show you information from Wiki Page. You can write only the topic name and information from the wiki page will be appeared. Enjoy, Kitty!";
			}else{
				$ans=$summary->text;
			}

		
		
		$myarr = array(
			'answer'  => $ans
		);
  
		$js=json_encode($myarr);
  
		return $js;

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

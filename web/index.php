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

	/* Here First I am retrieving data from the API
		
		Then Checking whether the question contains word temperature,humidity,rain,clouds or Clear
		
		If the question contains one of these words then return appropiate result
		
		otherwise return "not found"	
	
	*/
	
	$q=$_GET['q'];
  
	$arr = explode('<', $q);
	if(isset($arr[1]))
	{	
		$arr1 = explode('>', $arr[1]);
	}
	else return "Please Insert queston in appropiate form. Give city name inside < >. Sample question: What is today's humidity in <Dhaka>?";
	
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
			
			
			/*
				Here first trying to get data from DBpedia
			
			*/
  
			// here getting the SPARQL query
			$spar=file_get_contents("http://quepy.machinalis.com/engine/get_query?question=".urlencode($q));  
 			$decode_spar=json_decode($spar,false);
		
		
			//here getting DBpedia response
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
			
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($data));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

			$result = curl_exec($ch);
		
			echo "result found from: DBpedia.";
			echo "<br>";
			
			if(strlen(trim($result))<300) echo "Result Not Found.";
			else echo $result;
			
			echo "<br>";
			echo "<br>";
			echo "Result from Wiki:";
			echo "<br>";
			
			// here getting result from wiki using AYLIEN\TextAPI
			
			
			// frist try to find the keywords from the question ignoring the whitespaces,articles,preposition etc. 
			
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
			  $words = str_replace(',', '_', $words);
			  
			  echo "key words:".$words.". Search in AYLIEN\TextAPI will be done by this Topic";
			  echo "<br>";

			// here calling the AYLIEN\TextAPI with appid and key.
			
			  $textapi = new AYLIEN\TextAPI("098443aa", "e288284a802322d954b42740d0dfa95b");
			  $url='https://en.wikipedia.org/wiki/'.$words;
			  $summary= $textapi->summarize(array("url" => $url));
			  $ans="";
			  if($summary->text == null) {
				   $ans="Your majesty! Jon Snow knows nothing! So do I!.Result Also can't be found at AYLIEN\TextAPI. Sorry Kitty!!!";
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

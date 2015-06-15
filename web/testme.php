

<?php
define('APPLICATION_ID',"098443aa");
define('APPLICATION_KEY',"e288284a802322d954b42740d0dfa95b");

function call_api($endpoint, $parameters) {
  $ch = curl_init('https://api.aylien.com/api/v1/' . $endpoint);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'X-AYLIEN-TextAPI-Application-Key: ' . APPLICATION_KEY,
    'X-AYLIEN-TextAPI-Application-ID: '. APPLICATION_ID
  ));
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
  $response = curl_exec($ch);
  return json_decode($response);
}

$params = array('text' => 'John is a very good football player!');
$sentiment = call_api('sentiment', $params);
$language = call_api('language', $params);

echo sprintf("Sentiment: %s (%F)", $sentiment->polarity, $sentiment->polarity_confidence),
  PHP_EOL;
echo sprintf("Language: %s (%F)", $language->lang, $language->confidence),
  PHP_EOL;
?>


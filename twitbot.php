<?php
#!/usr/bin/env bash

//our search query to searched for in the Twitter timeline

/**
 * Read the Google Coordinates from Text or Google Map Still Debatable
 */
$isGeoEnabled=false; //geolocation
$geoCoords=null;
if($isGeoEnabled):
$geoCoords=array("lat","long","dist");
$geoCoords['lat']='6.513861';
$geoCoords['long']='3.403645';
$geoCoords['dist']='50m';	
endif;
/**
 * @todo Add a DataSource to read from the Database
 */
$search_query=array("MAU","UNILAG","Traffic");
//check that the file log exists if not create it for 

$file_name = 'last_tweet_number.txt';
// Create our twitter API object
require_once("twitteroauth.php");
// go to https://dev.twitter.com/apps and create new application
// and obtain [CONSUMER_KEY], [CONSUMER_SECRET], [oauth_token], [oauth_token_secret]
// then put them in place below
$oauth = new TwitterOAuth('[CONSUMER_KEY]', '[CONSUMER_SECRET]', '[oauth_token]', '[oauth_token_secret]');
// Send an API request to verify credentials
$credentials = $oauth->get("account/verify_credentials");
// Make up a useragent
$oauth->useragent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/3.6.0.9';
$remaining = $oauth->get('account/rate_limit_status');
// make sure we are banned by asking how many API hits we can make
echo "Current API hits remaining: {$remaining->remaining_hits}.\n";
// to prevent bot from responding to the same tweets over and over again
// we keep the since_id saved and pass it along when we search
$since_id = file_get_contents($file_name);
$configuration=array('q' => array_rand($search_query), 'since_id' => $since_id);
if($isGeoEnabled):
$config=array_merge($configuration,$geoCoords);	
endif;
$tweets_found = $oauth->get('http://search.twitter.com/search.json',isset($config)? $config:$configuration)->results;

	
// if a more recent tweet has appeared, store this tweet's id in the file
	//log the name to the filema
if (isset($tweets_found[0])) {
	file_put_contents($file_name, $tweets_found[0]->id_str);
}
foreach ($tweets_found as $tweet){
	$user = '@' . $tweet->from_user;
	echo $tweet->from_user . " says: ".$tweet->text."\r\n";
	//Statuses will be pulled from the Database with Topics Search and each response to it.
	$statuses = array (
	" we might be able to help - check out our website, lmk if your city is not listed",
	" awesomeeeeeeeeeeeeeeeeee",
	" what about putting an ad on findroomrent.com ?",
	" looking for a roomate? we can help" ,
	" we have some listings on our website, if you don't mind checking it out",
	" lmk, we can help, just put an ad in",
	);
	$status = "$user".$statuses[array_rand($statuses)];
	// do not respond to RT - retweets
	if (!startsWith($tweet->text,"RT")) {
echo "We responded: " . $status;
$oauth->post('statuses/update', array('status' => $status, 'in_reply_to_status_id' => $tweet->id_str));
	}
	// to simulate real person's behavoir make script pause for a while
	sleep(rand(120, 480));
}
 
// handy function for RT search
function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}



?>
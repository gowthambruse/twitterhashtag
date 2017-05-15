<?php



// Session start
session_start(); 

// Set timezone. (Modify to match your timezone) If you need help with this, you can find it here. (http://php.net/manual/en/timezones.php)
date_default_timezone_set('Europe/London');

// Require TwitterOAuth files. (Downloadable from : https://github.com/abraham/twitteroauth)
require_once("twitteroauth/twitteroauth/twitteroauth.php");

// Function to authenticate app with Twitter.
function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
  return $connection;
}

// Function to display the latest tweets.
function display_latest_tweets(

    // Function parameters.
    $twitter_user_id,
    $cache_file          = './tweets.txt',  // Change this to the path of your cache file. (Default : ./tweets.txt)
    $tweets_to_display   = 5,               // Number of tweets you would like to display. (Default : 5)
    $ignore_replies      = false,           // Ignore replies from the timeline. (Default : false)
    $include_rts         = false,           // Include retweets. (Default : false)
    $twitter_wrap_open   = '<ul class="home-tweets-ul">',
    $twitter_wrap_close  = '</ul>',
    $tweet_wrap_open     = '<li><p class="home-tweet-tweet">',
    $meta_wrap_open      = '<br/><span class="home-tweet-date">',
    $meta_wrap_close     = '</span>',
    $tweet_wrap_close    = '</p></li>',
    $date_format         = 'g:i A M jS',    // Date formatting. (http://php.net/manual/en/function.date.php)
    $twitter_style_dates = true){           // Twitter style days. [about an hour ago] (Default : true)

$consumerkey = "bzbbDZVxsbBZtiDlzRb7Vhoi3";
$consumersecret = "cqz37dZBMNbvKxFvwfykJ8dQthWtQ3lmu0vzrZaQlqRiSixkY2";
$accesstoken = "913054890-kXSu7l6qfhXmDCvK1p2iY9xvVGZz3PriYK70uTVs";
$accesstokensecret = "3WrI2VmtMv7CabR2ZFxyhC97PdgQH9ZUSemZXHtshkTME";



    // Seconds to cache feed (Default : 1 minute).
    $cachetime           = 60*3;

    // Time that the cache was last updtaed.
    $cache_file_created  = ((file_exists($cache_file))) ? filemtime($cache_file) : 0;

    // A flag so we know if the feed was successfully parsed.
    $tweet_found         = false;

    // Show cached version of tweets, if it's less than $cachetime.
    if (time() - $cachetime < $cache_file_created) {
        $tweet_found = true;
        // Display tweets from the cache.
        readfile($cache_file);       
    } else {

    // Cache file not found, or old. Authenticae app.
    $connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

        if($connection){
            // Get the latest tweets from Twitter
            $get_tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitter_user_id."&count=".$tweets_to_display."&include_rts=".$include_rts);

            // Error check: Make sure there is at least one item.
            if (count($get_tweets)) {

                // Define tweet_count as zero
                $tweet_count = 0;

                // Start output buffering.
                ob_start();

                // Open the twitter wrapping element.
                $twitter_html = $twitter_wrap_open;

                // Iterate over tweets.
                foreach($get_tweets as $tweet) {

                    // If we are not ignoring replies, or tweet is not a reply, process it.
                    if ($ignore_replies==false){

                        $tweet_found = true;
                        $tweet_count++;
                        $tweet_desc = $tweet->text;
                        // Add hyperlink html tags to any urls, twitter ids or hashtags in the tweet.
                        $tweet_desc = preg_replace('/(https?:\/\/[^\s"<>]+)/','<a href="$1" target="_blank">$1</a>',$tweet_desc);
                        $tweet_desc = preg_replace('/(^|[\n\s])@([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/$2" target="_blank">@$2</a>', $tweet_desc);
                        $tweet_desc = preg_replace('/(^|[\n\s])#([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/search?q=%23$2" target="_blank">#$2</a>', $tweet_desc);

                        // Convert Tweet display time to a UNIX timestamp. Twitter timestamps are in UTC/GMT time.
                        $tweet_time = strtotime($tweet->created_at);    
                        if ($twitter_style_dates){
                            // Current UNIX timestamp.
                            $current_time = time();
                            $time_diff = abs($current_time - $tweet_time);
                            switch ($time_diff) 
                            {
                                case ($time_diff < 60):
                                    $display_time = $time_diff.' seconds ago';                  
                                    break;      
                                case ($time_diff >= 60 && $time_diff < 3600):
                                    $min = floor($time_diff/60);
                                    $display_time = $min.' minutes ago';                  
                                    break;      
                                case ($time_diff >= 3600 && $time_diff < 86400):
                                    $hour = floor($time_diff/3600);
                                    $display_time = 'about '.$hour.' hour';
                                    if ($hour > 1){ $display_time .= 's'; }
                                    $display_time .= ' ago';
                                    break;          
                                default:
                                    $display_time = date($date_format,$tweet_time);
                                    break;
                            }
                        } else {
                            $display_time = date($date_format,$tweet_time);
                        }

                        // Render the tweet.
                        $twitter_html .= $tweet_wrap_open.html_entity_decode($tweet_desc).$meta_wrap_open.'<a href="http://twitter.com/'.$twitter_user_id.'">'.$display_time.'</a>'.$meta_wrap_close.$tweet_wrap_close;

                    }

                    // If we have processed enough tweets, stop.
                    if ($tweet_count >= $tweets_to_display){
                        break;
                    }

                }

                // Close the twitter wrapping element.
                $twitter_html .= $twitter_wrap_close;
                echo $twitter_html;

                // Generate a new cache file.
                $file = fopen($cache_file, 'w');

                // Save the contents of output buffer to the file, and flush the buffer. 
                fwrite($file, ob_get_contents()); 
                fclose($file); 
                ob_end_flush();

            }

        }

    }

}

// Display latest tweets. (Modify username to your Twitter handle)
display_latest_tweets('');
 ?>

<?php

// style and scripts

use function PHPSTORM_META\map;

add_action('wp_enqueue_scripts', 'bootscore_child_enqueue_styles');
function bootscore_child_enqueue_styles()
{

  // style.css
  wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

  // Compiled main.css
  $modified_bootscoreChildCss = date('YmdHi', filemtime(get_stylesheet_directory() . '/css/main.css'));
  wp_enqueue_style('main', get_stylesheet_directory_uri() . '/css/main.css', array('parent-style'), $modified_bootscoreChildCss);

  // custom.js
  wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/js/custom.js', false, '', true);
}

/**
 * Engage API for Displaying Events
 * 
 * API Documentation: https://engage-api.campuslabs.com/
 */

// Constants
define('ENGAGE_BASE_URL', 'https://engage-api.campuslabs.com/api/v3.0');
define('ENGAGE_API_KEY', 'esk_live_f98d79b42f2b22e3a9f9aacdcc4bf758');
define('ENGAGE_PAGE_SIZE', '50');
define('CUP_ORGANIZATION_ID', '280350'); // get this using the /organizations/organization endpoint
define('UTC_TIME', new DateTime('now', new DateTimeZone('UTC')));
define('UTC_TIMESTAMP', date_format(UTC_TIME, "c"));

// Engage Request
function engage_request($endpoint = '/organizations/organization', $args = array(), $method = 'GET', $body = '', $headers = array())
{
  $allArgs = array_merge(array(
    'take' => ENGAGE_PAGE_SIZE,
    'skip' => '0'
  ), $args);
  $allHeaders = array_merge(
    array(
      'Accept' => 'application/json',
      'X-Engage-Api-Key' => ENGAGE_API_KEY
    ),
    $headers
  );
  $full_url = ENGAGE_BASE_URL . $endpoint . '?' . http_build_query($allArgs);
  $request = wp_remote_request(
    $full_url,
    array(
      'method' => $method,
      'httpversion' => '1.1',
      'headers' => $allHeaders,
      'body' => $body
    )
  );
  $response_body = wp_remote_retrieve_body($request);
  $decoded_body = $response_body;
  return $decoded_body;
}

// // Concat Pages
// function concat_pages($endpoint = '/organizations/organization', $args = array(), $method = 'GET', $body = '', $headers = array('Accept' => 'application/json'))
// {
//   $objects = array();
//   $firstRequest = engage_request($endpoint, $args, $method, $body, $headers);
//   $firstRequestResponse = $firstRequest['response'];
//   $decodedFirstRequest = json_decode($firstRequestResponse);
//   $firstRequestItems = $decodedFirstRequest['items'];
//   $totalItems = $decodedFirstRequest['totalItems'];
//   foreach ($firstRequestItems as $firstRequestItem) {
//     array_push($objects, $firstRequestItem);
//   }
//   if ($totalItems > 50) {
//     $skip = 50;
//     $remaining = TRUE;
//     while ($remaining) {
//       $request = engage_request($endpoint, array_merge($args, array(
//         'skip' => $skip
//       )), $method, $body, $headers);
//       $response = $request['response'];
//       $decoded = json_decode($response, true);
//       $items = $decoded['items'];
//       foreach ($items as $item) {
//         array_push($objects, $item);
//       }
//       $totalItems = $decoded['totalItems'];
//       $remaining = $totalItems - $skip;
//       $skip = $totalItems - $remaining;
//     }
//   }
//   return $objects;
// }

// Hello World shortcode
function hello_world_function()
{
  $request = engage_request('/organizations/organization');
  $stuff = '<h1 class="h1">Hello world! Today is a great day!</h1>';
  $items = $request['items'];
  $card = '<div class="mt-3">';
  foreach(json_decode($items) as $item) {
    $card .= '<p><div class="card">';
    $card .= '<div class="card-body">';
    $card .= '<h5 class="card-title">' . $item['name'] . '</h5>';
    $card .= '<p class="card-text">';
    $card .= $item['email'];
    $card .= '</br>';
    $card .= $item['summary'];
    $card .= '</p></div></div></p>';
  }
  $card .= '</div>';
  return $card;
}
add_shortcode('hello_world', 'hello_world_function');

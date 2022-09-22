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
  $decoded_body = json_decode($response_body, true);
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

// CUP Members
function cup_events3_function()
{
  $request = engage_request('/events/event/', array(
    'organizationIds' => CUP_ORGANIZATION_ID,
    'excludeCoHosts' => 'false',
    'includeSubmissionIds' => 'true',
    'take' => '3',
    'skip' => '0'
  ));
  $items = $request['items'];
  $numOfCols = 3;
  $rowCount = 0;
  $bootstrapColWidth = 12 / $numOfCols;
  $card = '<div class="events">';
  foreach ($items as $item) {
    if ($rowCount % $numOfCols == 0) {
      $card .= '<div class="row mb-3 g-3">';
    }
    $rowCount++;
    $card .= '<div class="col col-md-' . $bootstrapColWidth . '">';
    $card .= '<div class="card">';
    if (strlen($item['imageUrl']) > 0) {
      $card .= '<div class="card-img-top"><img src="' . $item['imageUrl'] . '" alt="Event Image"></div>';
    }
    $card .= '<div class="card-body">';
    $card .= '<h3 class="card-title">' . $item['name'] . '</h3>';
    $card .= '<span class="card-text">';
    if (strlen($item['description']) > 0) {
      $card .= $item['description'];
    }
    $card .= '<a href="https://neu.campuslabs.com/engage/event/' . $item['id'] . '" target="_blank" class="btn btn-primary mt-3">View Event Details</a>';
    $card .= '</span></div></div></div>';
    if ($rowCount % $numOfCols == 0) {
      $card .= '</div>';
    }
  }
  $card .= '</div>';
  return $card;
}
add_shortcode('cup_events3', 'cup_events3_function');

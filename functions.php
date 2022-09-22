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
define('CUP_ORGANIZATION_ID', '280350'); // get this using the /organizations/organization endpoint
define('UTC_TIME', new DateTime('now', new DateTimeZone('UTC')));
define('UTC_TIMESTAMP', date_format(UTC_TIME, "c"));

/**
 * Define EngageApiRequest
 */
class Engage_Api extends WP_HTTP
{
  const BASE_URL = 'https://engage-api.campuslabs.com/api/v3.0';
  const API_KEY = 'esk_live_f98d79b42f2b22e3a9f9aacdcc4bf758';
  const PAGE_SIZE = '50';
  public $endpoint = '/organizations/member';
  public array $args;
  public $method = 'GET';
  public $body = '';
  public array $headers;

  public function engage_request($endpoint = NULL, $args = NULL, $method = NULL, $body = NULL, $headers = NULL)
  {
    if (is_null($endpoint)) {
      $endpoint = $this->endpoint;
    }
    if (is_null($args)) {
      $args = $this->args;
    }
    if (is_null($method)) {
      $method = $this->method;
    }
    if (is_null($body)) {
      $body = $this->body;
    }
    if (is_null($headers)) {
      $headers = $this->headers;
    }
    $allArgs = array_merge(array(
      'take' => self::PAGE_SIZE,
      'skip' => '0'
    ), $args);
    $allHeaders = array_merge(
      array(
        'Accept' => 'application/json',
        'X-Engage-Api-Key' => self::API_KEY
      ),
      $headers
    );
    $full_url = self::BASE_URL . $endpoint . http_build_query($allArgs);
    $request = WP_HTTP::request(
      $full_url,
      array(
        'method' => $method,
        'httpversion' => '1.1',
        'headers' => $allHeaders,
        'body' => $body
      )
    );

    return $request;
  }

  public function concat_pages($endpoint = NULL, $args = NULL, $method = NULL, $body = NULL, $headers = NULL)
  {
    if (is_null($endpoint)) {
      $endpoint = $this->endpoint;
    }
    if (is_null($args)) {
      $args = $this->args;
    }
    if (is_null($method)) {
      $method = $this->method;
    }
    if (is_null($body)) {
      $body = $this->body;
    }
    if (is_null($headers)) {
      $headers = $this->headers;
    }
    $objects = [];
    $firstRequest = $this->engage_request($endpoint, $args, $method, $body, $headers);
    $firstRequestResponse = $firstRequest['response'];
    $decodedFirstRequest = json_decode($firstRequestResponse);
    $firstRequestItems = $decodedFirstRequest['items'];
    $totalItems = $decodedFirstRequest['totalItems'];
    foreach ($firstRequestItems as $firstRequestItem) {
      array_push($objects, $firstRequestItem);
    }
    if ($totalItems > 50) {
      $skip = 50;
      $remaining = TRUE;
      while ($remaining) {
        $request = $this->engage_request($endpoint, array_merge($args, array(
          'skip' => $skip
        )), $method, $body, $headers);
        $response = $request['response'];
        $decoded = json_decode($response, true);
        $items = $decoded['items'];
        foreach ($items as $item) {
          array_push($objects, $item);
        }
        $totalItems = $decoded['totalItems'];
        $remaining = $totalItems - $skip;
        $skip = $totalItems - $remaining;
      }
    }
    return $objects;
  }
}

// Test events shortcode
function engage_events_function()
{
  $object = new Engage_Api('/events/event', array(
    'organizationIds' => CUP_ORGANIZATION_ID,
    'excludeCoHosts' => 'false',
    'includeSubmissionIds' => true
  ), 'GET');
  $request = $object->engage_request();
  return $request['response'];
}
add_shortcode('engage_events', 'engage_events_function');

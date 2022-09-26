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

// Returns a UTC timestamp
function utcTimestamp()
{
  $time = new DateTime('now', new DateTimeZone('UTC'));
  $timestamp = $time->format('c');
  return $timestamp;
}

// Engage Request
// Using the Engage API, make an HTTP request using the provided parameters.
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

// Engage Request Concat
// Concat a paged response from the Engage API into a single array.
function engage_request_concat($endpoint = '/organizations/organization', $args = array(), $method = 'GET', $body = '', $headers = array())
{
  // Initialize variables
  $allItems = array();
  $saved = 0;

  // Submit request to find total number of values
  $baseReq = engage_request($endpoint, array_merge(
    $args,
    array(
      'take' => ENGAGE_PAGE_SIZE,
      'skip' => strval($saved)
    )
  ), $method, $body, $headers);

  // Add the first batch of items
  $baseReqItems = $baseReq['items'];
  foreach ($baseReqItems as $baseReqItem) {
    $allItems[] = $baseReqItem;
    $saved++;
  }

  // Save the total number of items
  $totalItems = intval($baseReq['totalItems']);
  $remaining = $totalItems - $saved;

  // Iterate through additional pages
  while ($remaining > 0) {
    $request = engage_request($endpoint, array_merge($args, array(
      'take' => ENGAGE_PAGE_SIZE,
      'skip' => strval($saved)
    )), $method, $body, $headers);
    $items = $request['items'];
    foreach ($items as $item) {
      $allItems[] = $item;
      $saved++;
    }

    $remaining = $totalItems - $saved;
  }

  // Put response into array
  $response = array(
    'totalItems' => $baseReq['totalItems'],
    'items' => $allItems
  );
  return $response;
}

// CUP Events
function cup_events_function()
{
  $request = engage_request_concat('/events/event/', array(
    'organizationIds' => CUP_ORGANIZATION_ID,
    'excludeCoHosts' => 'false',
    'includeSubmissionIds' => 'true'
  ));
  $items = $request['items'];
  usort($items, function ($a, $b) {
    if ($a['startsOn'] == $b['startsOn']) return 0;
    return strtotime($b['startsOn']) - strtotime($a['startsOn']);
  });
  $card = '<div class="events">';
  foreach ($items as $item) {
    $card .= '<div class="event-wrapper">';
    $card .= '<div class="card event-card">';
    if (strlen($item['imageUrl']) > 0) {
      $card .= '<a href="https://neu.campuslabs.com/engage/event/' . $item['id'] . '" target="_blank"><div role="img" aria-label="Image Uploaded for Event Cover Photo" alt ="Image Uploaded for Event Cover Photo" class="card-img-top event-img" style="background-image: url(\'' . $item['imageUrl'] . '?preset=large-w\');"></div></a>';
    }
    $card .= '<div class="card-body">';
    $card .= '<h3 class="card-title">' . $item['name'] . '</h3>';
    $card .= '<span class="card-text">';
    if (strlen($item['description']) > 0) {
      $card .= $item['description'];
    }
    $card .= '<a href="https://neu.campuslabs.com/engage/event/' . $item['id'] . '" target="_blank" class="btn btn-primary mt-3">View Event Details</a>';
    $card .= '</span></div></div></div>';
  }
  $card .= '</div>';
  return $card;
}
add_shortcode('cup_events', 'cup_events_function');

// Homepage Events
function home_events_function()
{
  // Make the request for upcoming events
  $request = engage_request('/events/event/', array(
    'organizationIds' => CUP_ORGANIZATION_ID,
    'endsAfter' => utcTimestamp(),
    'excludeCoHosts' => 'false',
    'includeSubmissionIds' => 'true'
  ));

  // Seperate the returned item subarray from the total items value
  $items = $request['items'];
  $totalItems = intval($request['totalItems']);

  // Sort the returned events by start date/time
  usort($items, function ($a, $b) {
    if ($a['startsOn'] == $b['startsOn']) return 0;
    return strtotime($a['startsOn']) - strtotime($b['startsOn']);
  });

  // Keep only the first three events in the array
  if ($totalItems > 3) {
    $items = array_slice($items, 0, 3);
    $totalItems = 3;
  }

  // Show an alert if no upcoming events are scheduled
  if ($totalItems <= 0) {
    $html = '<div class="events home-events">';
    $html .= '<div class="alert alert-info no-events">';
    $html .= 'There are no scheduled upcoming events just yet. Stay tuned!';
    $html .= '</div></div>';
  } elseif ($totalItems > 0) {
    // Start HTML Row
    $html = '<div class="events home-events row g-3">';

    // code for each item (column)
    foreach ($items as $item) {
      // Convert start time format
      $eventStartTimeObject = new DateTimeImmutable($item['startsOn'], new DateTimeZone('UTC'));
      $eventStartTimeEST = $eventStartTimeObject->setTimezone(new DateTimeZone('America/New_York'));
      $eventStartTimeString = $eventStartTimeEST->format('l, F j \a\t g\:iA T');

      // event wrapper HTML
      $html .= '<div class="event-wrapper col-12 col-md-4">';
      $html .= '<div class="card border-dark event-card">';

      // event image if there is one
      if (strlen($item['imageUrl']) > 0) {
        $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $item['id'] . '" target="_blank">';
        $html .= '<div role="img" aria-label="Image Uploaded for Event Cover Photo" alt ="Image Uploaded for Event Cover Photo" class="card-img-top event-img border-bottom border-dark" style="background-image: url(\'' . $item['imageUrl'] . '?preset=large-w\');">';
        $html .= '</div></a>';
      }

      // event body
      $html .= '<div class="card-body event-body">';

      // event title
      $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $item['id'] . '" target="_blank">';
      $html .= '<h3 class="card-title event-title">' . $item['name'];
      $html .= '</h3></a>';

      // event text
      $html .= '<div class="card-text event-text">';

      // event date
      $html .= '<div class="event-date pb-1">';
      $html .= '<span class="bi bi-pe-1 bi-calendar2-heart">' . $eventStartTimeString;
      $html .= '</span></div>';

      // event location
      $html .= '<div class="event-location">';
      $html .= '<span class="bi bi-pe-1 bi-geo-alt-fill">' . $item['address']['name'];
      $html .= '</span></div>';

      // event button
      $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $item['id'] . '" class="btn btn-primary bi bi-pe-2 bi-box-arrow-up-right mt-3 event-button" target="_blank">';
      $html .= 'Learn More';
      $html .= '</a>';

      // closing tags for event body, card, and wrapper
      $html .= '</div></div></div';
    }

    // row closing tag
    $html .= '</div>';
  }

  // return the html
  return $html;
}
add_shortcode('home_events', 'home_events_function');

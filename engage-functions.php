<?php

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

// Cached Engage Request
// Make a request to the Engage API, or return a previously cached response.
function engage_request_cached($cacheName, $cacheExpires = 60, $endpoint = '/organizations/organization', $args = array(), $method = 'GET', $body = '', $headers = array())
{
    // Get any existing copy of our cached engage request
    if (false === ($request = get_transient($cacheName))) {
        // If a cached value does not exist, return the value of a new request and save that value as a new transient
        $request = engage_request($endpoint, $args, $method, $body, $headers);
        set_transient($cacheName, $request, $cacheExpires);
    }

    // Return the cached or new request value
    return $request;
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

// Cached Concatenated Engage Request
// Make a request to the Engage API and concatenate paged values, or return a previously cached response.
function engage_request_concat_cached($cacheName, $cacheExpires = 60, $endpoint = '/organizations/organization', $args = array(), $method = 'GET', $body = '', $headers = array())
{
    // Get any existing copy of our cached engage request
    if (false === ($request = get_transient($cacheName))) {
        // If a cached value does not exist, return the value of a new request and save that value as a new transient
        $request = engage_request_concat($endpoint, $args, $method, $body, $headers);
        set_transient($cacheName, $request, $cacheExpires);
    }

    // Return the cached or new request value
    return $request;
}

<?php

/**
 * Engage API for Displaying Events
 * 
 * API Documentation: https://engage-api.campuslabs.com/
 */

// Returns a UTC timestamp
function utcTimestampFunctions()
{
    $time = new DateTime('now', new DateTimeZone('UTC'));
    $timestamp = $time->format('c');
    return $timestamp;
}

// Returns the Engage API Key, or an error
function get_engage_api_key()
{
    $value = get_option('engage_api_key');
    if ($value == NULL || $value == false) {
        throw new WP_Error('engage_api_key_unset', 'You must set the Engage API Key under Settings -> Engage/Event Settings to make a request to the Engage API.');
    } else {
        return $value;
    }
}

// Engage Request
// Using the Engage API, make an HTTP request using the provided parameters.
function engage_request($endpoint = '/organizations/organization', $args = array(), $method = 'GET', $body = '', $headers = array())
{
    // Get the Engage API Key
    $engage_api_key = get_engage_api_key();

    // Merge given arguments with default arguments
    $allArgs = array_merge(array(
        'take' => ENGAGE_PAGE_SIZE,
        'skip' => '0'
    ), $args);

    // Merge given headers with default arguments
    $allHeaders = array_merge(
        array(
            'Accept' => 'application/json',
            'X-Engage-Api-Key' => $engage_api_key
        ),
        $headers
    );

    // Build the full endpoint URL
    $full_url = ENGAGE_BASE_URL . $endpoint . '?' . http_build_query($allArgs);

    // Make the request
    $request = wp_remote_request(
        $full_url,
        array(
            'method' => $method,
            'httpversion' => '1.1',
            'headers' => $allHeaders,
            'body' => $body
        )
    );

    // Retrieve the response body
    $response_body = wp_remote_retrieve_body($request);

    // Return the JSON decoded response body
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

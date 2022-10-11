<?php

// Homepage Events Shortcode
// Display the three closest upcoming events. For use on the homepage.
function home_events_function()
{
    // Make the request for upcoming events
    $request = engage_request_cached('homepage_events', 300, '/events/event/', array(
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

    // Start HTML variable
    $html = '';

    // Show an alert if no upcoming events are scheduled, or show events
    if ($totalItems <= 0) {
        $html .= '<div class="events home-events">';
        $html .= '<div class="alert alert-info no-events">';
        $html .= 'There are no scheduled upcoming events just yet. Stay tuned!';
        $html .= '</div></div>';
    } elseif ($totalItems > 0) {
        // Start events row
        $html .= '<div class="events home-events row g-3">';

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
                $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $item['id'] . '" class="event-img-link" target="_blank">';
                $html .= '<div role="img" aria-label="Image Uploaded for Event Cover Photo" alt ="Image Uploaded for Event Cover Photo" class="card-img-top event-img border-bottom border-dark" style="background-image: url(\'' . $item['imageUrl'] . '?preset=large-w\');">';
                $html .= '</div></a>';
            }

            // event body
            $html .= '<div class="card-body event-body">';

            // event title
            $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $item['id'] . '" class="event-title-link" target="_blank">';
            $html .= '<h3 class="card-title event-title">' . $item['name'];
            $html .= '</h3></a>';

            // event text
            $html .= '<div class="card-text event-text">';

            // event date
            $html .= '<div class="event-date pb-1">';
            $html .= '<span class="bi bi-pe-1 bi-calendar2-heart">' . $eventStartTimeString;
            $html .= '</span></div>';

            // event location
            $html .= '<div class="event-location pb-3">';
            $html .= '<span class="bi bi-pe-1 bi-geo-alt-fill">' . $item['address']['name'];
            $html .= '</span></div>';

            // event button
            $html .= '<div class="event-button">';
            $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $item['id'] . '" class="btn btn-primary bi bi-pe-2 bi-box-arrow-up-right" target="_blank">';
            $html .= 'Learn More';
            $html .= '</a></div>';

            // closing tags for event text, body, card, and wrapper
            $html .= '</div></div></div></div>';
        }

        // row closing tag
        $html .= '</div>';
    }

    // return the html
    return $html;
}
add_shortcode('home_events', 'home_events_function');

// Upcoming Events Shortcode
// Display cards for all upcoming events.
function upcoming_events_function()
{
    // Make the request for upcoming events
    $request = engage_request_concat_cached('upcoming_events', 300, '/events/event/', array(
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

    // Split the array of events into chunks of 3
    $items = array_chunk($items, 3);

    // Start HTML variable
    $html = '';

    // Show an alert if no upcoming events are scheduled, or show events
    if ($totalItems <= 0) {
        $html .= '<div class="events upcoming-events">';
        $html .= '<div class="alert alert-info no-events">';
        $html .= 'There are no scheduled upcoming events just yet. Stay tuned!';
        $html .= '</div></div>';
    } elseif ($totalItems > 0) {
        // All upcoming events wrapper
        $html .= '<div class="events upcoming-events">';
        // Cycle through each row
        foreach ($items as $row) {
            // Start new row
            $html .= '<div class="event-row row g-3 pb-3">';

            foreach ($row as $event) {
                // Convert start time format
                $eventStartTimeObject = new DateTimeImmutable($event['startsOn'], new DateTimeZone('UTC'));
                $eventStartTimeEST = $eventStartTimeObject->setTimezone(new DateTimeZone('America/New_York'));
                $eventStartTimeString = $eventStartTimeEST->format('l, F j \a\t g\:iA T');

                // event wrapper HTML
                $html .= '<div class="event-wrapper col-12 col-md-4">';
                $html .= '<div class="card border-dark event-card">';

                // event image if there is one
                if (strlen($event['imageUrl']) > 0) {
                    $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $event['id'] . '" class="event-img-link" target="_blank">';
                    $html .= '<div role="img" aria-label="Image Uploaded for Event Cover Photo" alt ="Image Uploaded for Event Cover Photo" class="card-img-top event-img border-bottom border-dark" style="background-image: url(\'' . $event['imageUrl'] . '?preset=large-w\');">';
                    $html .= '</div></a>';
                }

                // event body
                $html .= '<div class="card-body event-body">';

                // event title
                $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $event['id'] . '" class="event-title-link" target="_blank">';
                $html .= '<h3 class="card-title event-title">' . $event['name'];
                $html .= '</h3></a>';

                // event text
                $html .= '<div class="card-text event-text">';

                // event date
                $html .= '<div class="event-date pb-1">';
                $html .= '<span class="bi bi-pe-1 bi-calendar2-heart">' . $eventStartTimeString;
                $html .= '</span></div>';

                // event location
                $html .= '<div class="event-location pb-3">';
                $html .= '<span class="bi bi-pe-1 bi-geo-alt-fill">' . $event['address']['name'];
                $html .= '</span></div>';

                // event button
                $html .= '<div class="event-button">';
                $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $event['id'] . '" class="btn btn-primary bi bi-pe-2 bi-box-arrow-up-right" target="_blank">';
                $html .= 'Learn More';
                $html .= '</a></div>';

                // closing tags for event text, body, card, and wrapper
                $html .= '</div></div></div></div>';
            }

            // Row closing tag
            $html .= '</div>';
        }
        // All events closing tag
        $html .= '</div>';
    }
    // return the html
    return $html;
}
add_shortcode('upcoming_events', 'upcoming_events_function');

// Past Events Shortcode
// Display cards for all past events.
function past_events_function()
{
    // Make the request for upcoming events
    $request = engage_request_concat('/events/event/', array(
        'organizationIds' => CUP_ORGANIZATION_ID,
        'endsBefore' => utcTimestamp(),
        'startsAfter' => '2022-09-01',
        'excludeCoHosts' => 'false',
        'includeSubmissionIds' => 'true'
    ));

    // Seperate the returned item subarray from the total items value
    $items = $request['items'];
    $totalItems = intval($request['totalItems']);

    // Sort the returned events in descending order by start date/time
    usort($items, function ($a, $b) {
        if ($b['startsOn'] == $a['startsOn']) return 0;
        return strtotime($b['startsOn']) - strtotime($a['startsOn']);
    });

    // Split the array of events into chunks of 3
    $items = array_chunk($items, 4);

    // Start HTML variable
    $html = '';

    // Show an alert if no upcoming events are scheduled, or show events
    if ($totalItems <= 0) {
        $html .= '<div class="events past-events">';
        $html .= '<div class="alert alert-info no-events">';
        $html .= 'There are no past events.';
        $html .= '</div></div>';
    } elseif ($totalItems > 0) {
        // All upcoming events wrapper
        $html .= '<div class="events past-events">';
        // Cycle through each row
        foreach ($items as $row) {
            // Start new row
            $html .= '<div class="event-row row g-3 pb-0 pb-md-3">';

            foreach ($row as $event) {
                // Convert start time format
                $eventStartTimeObject = new DateTimeImmutable($event['startsOn'], new DateTimeZone('UTC'));
                $eventStartTimeEST = $eventStartTimeObject->setTimezone(new DateTimeZone('America/New_York'));
                $eventStartTimeString = $eventStartTimeEST->format('l, F j \a\t g\:iA T');

                // event wrapper HTML
                $html .= '<div class="event-wrapper col-12 col-md-3">';
                $html .= '<div class="card border-dark event-card">';

                // event image if there is one
                if (strlen($event['imageUrl']) > 0) {
                    $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $event['id'] . '" class="event-img-link" target="_blank">';
                    $html .= '<div role="img" aria-label="Image Uploaded for Event Cover Photo" alt ="Image Uploaded for Event Cover Photo" class="card-img-top event-img border-bottom border-dark" style="background-image: url(\'' . $event['imageUrl'] . '?preset=large-w\');">';
                    $html .= '</div></a>';
                }

                // event body
                $html .= '<div class="card-body event-body">';

                // event title
                $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $event['id'] . '" class="event-title-link" target="_blank">';
                $html .= '<h3 class="card-title event-title"><small>' . $event['name'];
                $html .= '</small></h3></a>';

                // event text
                $html .= '<div class="card-text event-text">';

                // event date
                $html .= '<div class="event-date pb-1">';
                $html .= '<small><span class="bi bi-pe-1 bi-calendar2-heart">' . $eventStartTimeString;
                $html .= '</span></small></div>';

                // event location
                $html .= '<div class="event-location pb-3">';
                $html .= '<small><span class="bi bi-pe-1 bi-geo-alt-fill">' . $event['address']['name'];
                $html .= '</span></small></div>';

                // event button
                $html .= '<div class="event-button">';
                $html .= '<a href="https://neu.campuslabs.com/engage/event/' . $event['id'] . '" class="btn btn-primary btn-sm bi bi-pe-2 bi-box-arrow-up-right" target="_blank">';
                $html .= 'Learn More';
                $html .= '</a></div>';

                // closing tags for event text, body, card, and wrapper
                $html .= '</div></div></div></div>';
            }

            // Row closing tag
            $html .= '</div>';
        }
        // All events closing tag
        $html .= '</div>';
    }
    // return the html
    return $html;
}
add_shortcode('past_events', 'past_events_function');

<?php

// Hook for adding the engage settings submenu
add_action('admin_menu', 'engage_settings_menu');

// Action function for above hook
function engage_settings_menu()
{
    // Add a new options page as a submenu of the Settings main menu
    add_options_page('Engage Settings', 'Engage Settings', 'manage_options', 'engage-settings-menu', 'engage_settings_page');
}

// Displays the page content for the Engage Settings submenu
function engage_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">';
    echo '<h1>CampusLabs Engage Settings</h1>';
    echo '<p>Events are displayed throughout the site from the CampusLabs Engage Platform, using their ';
    echo 'REST API. New events will be automatically pulled every 5 minutes. Configure the integration ';
    echo 'settings below if needed, but these should not need to change often.</p>';
    echo '<h2 class="title">Integration Settings</h2>';
    echo '<h2 class="title">Available Shortcodes</h2>';
    echo '<p>Currently, three shortcodes are available:';
    echo '<ol>';
    echo '<li><code>[homepage_events]</code> - Displays the next three upcoming events</li>';
    echo '<li><code>[upcoming_events]</code> - Displays all upcoming events</li>';
    echo '<li><code>[past_events]</code> - Displays past events that take place after the specified cutoff date</li>';
    echo '<h2 class="title">Creating an Event</h2>';
    echo '<p>To add new events, you must have an assigned position on CUP\'s roster. New events can be created ';
    echo 'by going to <a href="https://neu.campuslabs.com/engage/">Engage</a>, clicking "CUP" under ';
    echo '"My Organizations", and clicking "Events" under "Organization Tools". Once you go through the ';
    echo 'event creation process, an email will be sent to the CUP advisor to confirm the event details are ';
    echo 'valid, and once approved, the event will automatically posted on the CUP website, the Student Hub, ';
    echo 'and other various Northeastern platforms.</p>';
    echo '</div>';
}

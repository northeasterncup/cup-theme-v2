<?php

// Hook for adding the engage settings submenu
add_action('admin_menu', 'engage_admin_add_page');

// Action function for above hook
function engage_admin_add_page()
{
    // Add a new options page as a submenu of the Settings main menu
    add_options_page('Engage Settings Page', 'Engage Settings Menu', 'manage_options', 'engage', 'engage_options_page');
}

// Displays the page content for the Engage Settings submenu
function engage_options_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">';
    echo '<h1>CampusLabs Engage Settings</h1>';
    echo '<p>Events are displayed throughout the site from the CampusLabs Engage Platform, using their ';
    echo 'REST API. New events will be automatically pulled every 5 minutes. Configure the integration ';
    echo 'settings below if needed, but these should not need to change often.</p>';
    echo '<h2 class="title">API Integration Settings</h2>';
    echo '<form method="post" action="options.php">';
    settings_fields('engage_options');
    do_settings_sections('engage');
    submit_button('Save Changes');
    echo '</form>';
    echo '<h2 class="title">Available Shortcodes</h2>';
    echo '<p>Currently, three shortcodes are available:';
    echo '<ol>';
    echo '<li><code>[homepage_events]</code> - Displays the next three upcoming events</li>';
    echo '<li><code>[upcoming_events]</code> - Displays all upcoming events</li>';
    echo '<li><code>[past_events]</code> - Displays past events that take place after the specified cutoff date</li>';
    echo '</ol>';
    echo '<h2 class="title">Creating an Event</h2>';
    echo '<p>To add new events, you must have an assigned position on CUP\'s roster. New events can be created ';
    echo 'by going to <a href="https://neu.campuslabs.com/engage/">Engage</a>, clicking "CUP" under ';
    echo '"My Organizations", and clicking "Events" under "Organization Tools". Once you go through the ';
    echo 'event creation process, an email will be sent to the CUP advisor to confirm the event details are ';
    echo 'valid, and once approved, the event will automatically posted on the CUP website, the Student Hub, ';
    echo 'and other various Northeastern platforms.</p>';
    echo '</div>';
}

// Hook for registering the engage settings
add_action('admin_init', 'engage_admin_init');

// Action function for the above hook
function engage_admin_init()
{
    // Register Engage API Settings
    register_setting('engage_options', 'engage_options', 'engage_options_validate');

    // Register Engage API Settings Section
    add_settings_section(
        'engage_api',
        'API Settings',
        'engage_section_text',
        'engage'
    );

    // Register Register Engage API Settings Fields
    add_settings_field(
        'engage_base_url',
        'Base URL',
        'engage_setting_base_url',
        'engage',
        'engage_api'
    );
}

function engage_section_text()
{
    echo '<p>Manage the API integration settings.</p>';
}

// Callback for the base url settings field
function engage_setting_base_url()
{
    $options = get_option('engage_options');
    echo "<input id='engage_base_url' name='engage_options[base_url]' size='40' type='text' value='{$options['base_url']}' />";
}

// Validation function for the base url settings field
function engage_options_validate($input)
{
    $options = get_option('engage_options');
    $options['base_url'] = trim($input['base_url']);
    if (!preg_match('/^[a-z0-9]{32}$/i', $options['base_url'])) {
        $options['base_url'] = '';
    }
    return $options;
}

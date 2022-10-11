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
    echo '<form method="post" action="options.php">';
    settings_fields('engage_settings');
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
    // Register Base URL
    register_setting('engage_settings', 'engage_base_url', array(
        'type' => 'string',
        // 'sanitize_callback' => 'engage_options_validate',
        'show_in_rest' => FALSE,
        'default' => NULL
    ));

    // Register Engage API Settings Section
    add_settings_section(
        'engage_api',
        'API Settings',
        'engage_api_text',
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

// Callback for the API settings section text
function engage_api_text()
{
    echo '<p>Manage the API integration settings.</p>';
}

// Callback for the base url settings field
function engage_setting_base_url()
{
    $setting = get_option('engage_base_url');
?>
    <input type="url" name="engage_base_url" pattern="https://.*" size="40" placeholder="https://engage-api.campuslabs.com/api/v3.0" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
<?
}

// // Validation function for all engage options
// function engage_options_validate($input)
// {
//     $newinput['base_url'] = trim($input['base_url']);
//     if (!preg_match('/^[a-z0-9]{32}$/i', $newinput['base_url'])) {
//         $newinput['base_url'] = '';
//     }
//     return $newinput;
// }

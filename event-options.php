<?php

// Hook for adding the engage settings submenu
add_action('admin_menu', 'engage_admin_add_page');

// Action function for above hook
function engage_admin_add_page()
{
    // Add a new options page as a submenu of the Settings main menu
    add_options_page('Engage/Event Settings', 'Engage/Event Settings', 'manage_options', 'engage', 'engage_options_page');
}

// Displays the page content for the Engage Settings submenu
function engage_options_page()
{
    // Check whether the current user can manage options
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Check whether the reset event cache button has been pressed AND also check the nonce
    if (isset($_POST['clear_event_cache']) && check_admin_referer('clear_event_cache_clicked')) {
        clear_event_cache();
    }

    echo '<div class="wrap">';
    echo '<h1>CampusLabs Engage Settings</h1>';
    echo '<p>Events are displayed throughout the site from the CampusLabs Engage Platform, using their ';
    echo 'REST API. New events will be automatically pulled every 5 minutes. Configure the integration ';
    echo 'settings below if needed, but these should not need to change often.</p>';

    // Engage Settings Form
    echo '<form method="post" action="options.php">';
    settings_fields('engage_settings');
    do_settings_sections('engage');
    submit_button('Save Changes');
    echo '</form>';

    // Reset Event Cache Form
    echo '<h2>Reset Event Cache</h2>';
    echo '<p>Events are automatically pulled every 5 minutes. If you would like to pull new events now, ';
    echo 'click the button below.';

    echo '<form action="options-general.php?page=engage" method="post">';
    wp_nonce_field('clear_event_cache_clicked');
    echo '<input type="hidden" value="true" name="clear_event_cache" />';
    submit_button('Clear Event Cache');
    echo '</form>';

    // Available Shortcodes Description
    echo '<h2 class="title">Available Shortcodes</h2>';
    echo '<p>Currently, three shortcodes are available:';
    echo '<ol>';
    echo '<li><code>[homepage_events]</code> - Displays the next three upcoming events</li>';
    echo '<li><code>[upcoming_events]</code> - Displays all upcoming events</li>';
    echo '<li><code>[past_events]</code> - Displays past events that take place after the specified cutoff date</li>';
    echo '</ol>';

    // Creating an Event Instructions
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

    // Register API Key setting
    register_setting('engage_settings', 'engage_api_key', array(
        'type' => 'string',
        'show_in_rest' => FALSE,
        'default' => NULL
    ));

    // Register CUP Organization ID setting
    register_setting('engage_settings', 'engage_cup_org_id', array(
        'type' => 'string',
        'show_in_rest' => FALSE,
        'default' => NULL
    ));

    // Register Event Cutoff setting
    register_setting('engage_settings', 'engage_event_cutoff', array(
        'type' => 'string',
        'show_in_rest' => FALSE,
        'default' => NULL
    ));

    // Register Engage API settings section
    add_settings_section(
        'engage_api',
        'API Integration Settings',
        'engage_api_text',
        'engage'
    );

    // Register Event Display settings section
    add_settings_section(
        'engage_event_display',
        'Event Display Settings',
        'engage_event_display_text',
        'engage'
    );

    // Register API Key field
    add_settings_field(
        'engage_api_key',
        'API Key',
        'engage_setting_api_key',
        'engage',
        'engage_api'
    );

    // Register API Key field
    add_settings_field(
        'engage_cup_org_id',
        'CUP Organization ID',
        'engage_setting_cup_org_id',
        'engage',
        'engage_api'
    );

    // Register Event Cutoff field
    add_settings_field(
        'engage_event_cutoff',
        'Event Cutoff Date',
        'engage_setting_event_cutoff',
        'engage',
        'engage_event_display'
    );
}

// Callback for the API settings section text
function engage_api_text()
{
    echo '<p>Settings used to pull events from the Engage API. Documentation from CampusLabs can be found <a href="">here</a>.</p>';
}

// Callback for the Event Display settings section text
function engage_event_display_text()
{
    echo '<p>Settings for how events will be displayed throughout the site.</p>';
}

// Callback for the api key settings field
function engage_setting_api_key()
{
    $setting = get_option('engage_api_key');
?>
    <input type="text" name="engage_api_key" size="60" placeholder="e.g. esk_test_3ef94b252b22047586dc53307a10580e" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>" required>
    <p class="description">
        The API key received from a CSI admin with at minimum <code>GET</code> access to the <code>/events</code> endpoint. This should not change unless an API key expires.
    </p>
<?
}

// Callback for the CUP Organization ID settings field
function engage_setting_cup_org_id()
{
    $setting = get_option('engage_cup_org_id');
?>
    <input type="text" name="engage_cup_org_id" size="10" placeholder="e.g. 202334" value="<?php echo isset($setting) ? esc_attr($setting) : '280350'; ?>" required>
    <p class="description">
        CUP's Organization ID. Will almost certainly never change. Can be found by doing a <code>GET</code> request to the <code>/organizations/organization</code> endpoint.
    </p>
<?
}

// Callback for the event cutoff settings field
function engage_setting_event_cutoff()
{
    $setting = get_option('engage_event_cutoff');
?>
    <input type="date" name="engage_event_cutoff" max="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($setting) ? esc_attr($setting) : '2022-09-01'; ?>" required>
    <p class="description">
        Past events before this date will not be shown in the <code>[past_events]</code> shortcode.
    </p>
<?
}

// Clear the event shortcode transients
function clear_event_cache()
{
    // Delete the transients
    delete_transient('homepage_events');
    delete_transient('upcoming_events');
    delete_transient('past_events');

    // Tell the user that the cache has been cleared
    echo '<div id="message" class="updated fade"><p>';
    echo 'The event cache has been cleared.' . '</p></div>';
}

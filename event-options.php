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
    echo '<p>Here is where the form would go if I actually had options.</p>';
    echo '</div>';
}

<?php

class FCBJC_Admin
{
    private static $instance;
    private $settings_options;
    private $lead_controller;

    private function __construct()
    {
        wp_register_style('fcbjc_style', plugins_url('/css/style.css', __FILE__));
        wp_enqueue_style('fcbjc_style');
        $this->lead_controller = FCBJC_Lead_Controller::instance();
        add_action('admin_menu', array($this, 'fcbjc_plugin_pages'));
        add_action('admin_init', array($this, 'fcbjc_settings_init'));
    }

    public static function instance(): FCBJC_Admin
    {
        if (!isset(self::$instance)) {
            self::$instance = new FCBJC_Admin();
        }
        return self::$instance;
    }

    public function fcbjc_plugin_pages()
    {
        add_menu_page('Floating Contact Button', 'Floating Contact Button', 'manage_options', 'fcbjc-dashboard-handle', array($this, 'create_dashboard_page'));
        add_submenu_page('fcbjc-dashboard-handle', 'Dashboard', 'Dashboard', 'manage_options', 'fcbjc-dashboard-handle', array($this, 'create_dashboard_page'));
        add_submenu_page('fcbjc-dashboard-handle', 'Settings', 'Settings', 'manage_options', 'fcbjc-settings-handle', array($this, 'create_settings_page'));
    }

    public function create_dashboard_page()
    { ?>
        <div>
            <h2>Dashboard</h2>
            <h3>Unreaded Leads</h3>
            <div class="lead_table_container">
                <table class="lead_table">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Created at</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    $unreaded_leads = $this->lead_controller->db_unreaded();
                    foreach ($unreaded_leads as $lead) {
                        $this->lead_record_callback($lead);
                    }
                    ?>
                </table>
            </div>
            <h3>Readed Leads</h3>
            <div class="lead_table_container">
                <table class="lead_table">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Created at</th>
                        <th>Readed at</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    $readed_leads = $this->lead_controller->db_readed();
                    foreach ($readed_leads as $lead) {
                        $this->lead_readed_record_callback($lead);
                    }
                    ?>
                </table>
            </div>
        </div>
    <?php
    }

    public function lead_record_callback($lead)
    {
        echo "<tr class=\"unreaded\"><td>$lead->id</td><td>$lead->name</td><td>$lead->email</td><td class=\"lead_table_message\">$lead->message</td><td style=\"text-wrap: nowrap;\">$lead->created_at</td><td><button>Readed</button></td></tr>";
    }

    public function lead_readed_record_callback($lead)
    {
        echo "<tr><td>$lead->id</td><td>$lead->name</td><td>$lead->email</td><td class=\"lead_table_message\">$lead->message</td><td style=\"text-wrap: nowrap;\">$lead->created_at</td><td style=\"text-wrap: nowrap;\">$lead->readed_at</td><td><button>Unreaded</button></td></tr>";
    }

    public function create_settings_page()
    {
        $this->settings_options = get_option('fcbjc_settings');
    ?>
        <div class="wrap">
            <h2>Settings</h2>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('fcbjc_option_group');
                do_settings_sections('fcbjc-settings-admin');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    private function on_active()
    {
        $this->settings_options = get_option('fcbjc_settings');
        if (!isset($this->settings_options['open_description_text'])) {
            $intial_settings = array(
                'call_number' => '',
                'whatsapp_number' => '',
                'open_description_text' => 'CONTACT US',
                'open_description_color' => '#000000',
                'text_color' => '#ffffff',
                'background_active_color' => '#f31a2b',
                'background_color' => '#000000',
            );
            update_option('fcbjc_settings', $intial_settings);
        }
    }

    public function fcbjc_settings_init()
    {
        register_setting(
            'fcbjc_option_group',
            'fcbjc_settings',
            array($this, 'settings_sanitize')
        );

        add_settings_section(
            'fcbjc_settings_contact_information_section',
            '',
            array($this, 'settings_contact_information_section'),
            'fcbjc-settings-admin'
        );

        add_settings_field(
            'call_number',
            'Call Number',
            array($this, 'call_number_callback'),
            'fcbjc-settings-admin',
            'fcbjc_settings_contact_information_section'
        );

        add_settings_field(
            'whatsapp_number',
            'Whatsapp Number',
            array($this, 'whatsapp_number_callback'),
            'fcbjc-settings-admin',
            'fcbjc_settings_contact_information_section'
        );

        add_settings_section(
            'fcbjc_settings_personalization_section',
            '',
            array($this, 'settings_personalization_section'),
            'fcbjc-settings-admin'
        );

        add_settings_field(
            'open_description_text',
            'Open Description Text',
            array($this, 'open_description_text_callback'),
            'fcbjc-settings-admin',
            'fcbjc_settings_personalization_section'
        );

        add_settings_field(
            'open_description_color',
            'Open Description Color',
            array($this, 'open_description_color_callback'),
            'fcbjc-settings-admin',
            'fcbjc_settings_personalization_section'
        );

        add_settings_field(
            'text_color',
            'Text Color',
            array($this, 'text_color_callback'),
            'fcbjc-settings-admin',
            'fcbjc_settings_personalization_section'
        );

        add_settings_field(
            'background_active_color',
            'Background Active Color',
            array($this, 'background_active_color_callback'),
            'fcbjc-settings-admin',
            'fcbjc_settings_personalization_section'
        );

        add_settings_field(
            'background_color',
            'Background Color',
            array($this, 'background_color_callback'),
            'fcbjc-settings-admin',
            'fcbjc_settings_personalization_section'
        );

        if (get_transient('fcbjc-active-admin-notice')) {
            $this->on_active();
        }
    }

    public function settings_sanitize($input)
    {
        $sanitary_values = array();
        if (isset($input['call_number'])) {
            $sanitary_values['call_number'] = sanitize_text_field($input['call_number']);
        }

        if (isset($input['whatsapp_number'])) {
            $sanitary_values['whatsapp_number'] = sanitize_text_field($input['whatsapp_number']);
        }

        if (isset($input['open_description_text'])) {
            $sanitary_values['open_description_text'] = sanitize_text_field($input['open_description_text']);
        }

        if (isset($input['open_description_color'])) {
            $sanitary_values['open_description_color'] = sanitize_text_field($input['open_description_color']);
        }

        if (isset($input['text_color'])) {
            $sanitary_values['text_color'] = sanitize_text_field($input['text_color']);
        }

        if (isset($input['background_active_color'])) {
            $sanitary_values['background_active_color'] = sanitize_text_field($input['background_active_color']);
        }

        if (isset($input['background_color'])) {
            $sanitary_values['background_color'] = sanitize_text_field($input['background_color']);
        }

        return $sanitary_values;
    }

    public function settings_contact_information_section()
    {
    ?>
        <h2>Contact information</h2>
    <?php
    }

    public function call_number_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="fcbjc_settings[call_number]" id="call_number" value="%s">',
            isset($this->settings_options['call_number']) ? esc_attr($this->settings_options['call_number']) : ''
        );
    }

    public function whatsapp_number_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="fcbjc_settings[whatsapp_number]" id="whatsapp_number" value="%s">',
            isset($this->settings_options['whatsapp_number']) ? esc_attr($this->settings_options['whatsapp_number']) : ''
        );
    }

    public function settings_personalization_section()
    {
    ?>
        <h2>Personalization</h2>
<?php
    }

    public function open_description_text_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="fcbjc_settings[open_description_text]" id="open_description_text" value="%s">',
            isset($this->settings_options['open_description_text']) ? esc_attr($this->settings_options['open_description_text']) : ''
        );
    }

    public function open_description_color_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="fcbjc_settings[open_description_color]" id="open_description_color" value="%s">',
            isset($this->settings_options['open_description_color']) ? esc_attr($this->settings_options['open_description_color']) : ''
        );
    }

    public function text_color_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="fcbjc_settings[text_color]" id="text_color" value="%s">',
            isset($this->settings_options['text_color']) ? esc_attr($this->settings_options['text_color']) : ''
        );
    }

    public function background_active_color_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="fcbjc_settings[background_active_color]" id="background_active_color" value="%s">',
            isset($this->settings_options['background_active_color']) ? esc_attr($this->settings_options['background_active_color']) : ''
        );
    }

    public function background_color_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="fcbjc_settings[background_color]" id="background_color" value="%s">',
            isset($this->settings_options['background_color']) ? esc_attr($this->settings_options['background_color']) : ''
        );
    }
}

/* 
 * Retrieve this value with:
 * $settings_options = get_option( 'fcbjc_settings' ); // Array of All Options
 * $open_description_text = $settings_options['open_description_text']; // Open Description Text
 * $open_description_color = $settings_options['open_description_color']; // Open Description Color
 */

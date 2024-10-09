<?php
include "includes/icons_svg.php";

class FCBJC_User
{
    private static $instance;

    private function __construct()
    {
        wp_register_style('fcbjc_user_style', plugins_url('/css/style.css', __FILE__));
        wp_enqueue_style('fcbjc_user_style');
        wp_register_script('fcbjc_user_script', plugins_url('/js/script.js', __FILE__));
        wp_enqueue_script('fcbjc_user_script');
        add_action('wp_footer', array($this, 'init'));
    }

    public static function instance(): FCBJC_User
    {
        if (!isset(self::$instance)) {
            self::$instance = new FCBJC_User();
        }
        return self::$instance;
    }

    public function init()
    {
        $settings_options = get_option('fcbjc_settings');
        $open_description_text = $settings_options['open_description_text'];
        $open_description_color = $settings_options['open_description_color'];
        $text_color = $settings_options['text_color'];
        $background_active_color = $settings_options['background_active_color'];
        $background_color = $settings_options['background_color'];
        $call_number = $settings_options['call_number'];
        $whatsapp_number = $settings_options['whatsapp_number'];
        echo '<div id="fcbjc_container" class="fcbjc_container">
        <input id="fcbjc_background_active_color" name="fcbjc_background_active_color" type="hidden" value="' . $background_active_color . '">
        <input id="fcbjc_host_url" type="hidden" value="' . get_site_url() . '">
        <div id="fcbjc_background" class="fcbjc_background" style="display: none"></div>
        ' . $this->bar($open_description_text, $open_description_color, $text_color, $background_active_color, $background_color, $call_number) . '
        ' . $this->popup($background_active_color, $whatsapp_number, $text_color, $background_color) . '
        </div>';
    }

    private function bar($open_description_text, $open_description_color, $text_color, $background_active_color, $background_color, $call_number)
    {
        $bar_button_text_tag = ' <span class="fcbjc_bar_button_text" style="color: ' . $text_color . ';">';
        return '<div id="fcbjc_open_bar" class="fcbjc_open_bar">
            <div class="fcbjc_open_bar_description" style="color: ' . $open_description_color . ';">' . $open_description_text . '</div>
            <div class="fcbjc_open_bar_button" style="background-color: ' . $background_active_color . ';">
                <div class="">' . up_arrow_svg($text_color) . '</div>
            </div>
        </div>
        <div id="fcbjc_bar" class="fcbjc_bar" style="display: none; background-color: ' . $background_color . ';">
            <a id="fcbjc_call_button" class="fcbjc_bar_button" href="tel:' . $call_number . '">' . phone_svg($text_color) . $bar_button_text_tag . 'CALL US</span></a>
            <div id="fcbjc_contact_button" class="fcbjc_bar_button">' . agent_svg($text_color) . $bar_button_text_tag . 'CONTACT US</span></div>
            <div id="fcbjc_chat_button" class="fcbjc_bar_button">' . chat_svg($text_color) . $bar_button_text_tag . 'CHAT WITH US</span></div>
            <div id="fcbjc_close_bar_button" class="fcbjc_close_bar_button">' . down_arrow_svg($text_color) . '</div>
        </div>';
    }

    private function popup($background_active_color, $whatsapp_number, $text_color, $background_color)
    {
        return '<div id="fcbjc_popup" class="fcbjc_popup" style="display: none; background-color: ' . $background_color . '">
        <div id="fcbjc_lead_container" class="fcbjc_lead_container" style="display: none">
            <div id="fcbjc_lead_form" class="fcbjc_lead_form">
                <style type="text/css">
                input, textarea {
                    color: ' . $text_color . ';
                    border: solid ' . $text_color . ' 1px;
                }
                input:focus-visible, textarea:focus-visible {
                    outline: 2px solid ' . $background_active_color . ';
                }
                </style>
                <h2>Contact</h2>
                <p id="fcbjc_lead_error" class="fcbjc_lead_error" style="display: none">Please fill all fields</p>
                <input id="fcbjc_lead_name" class="fcbjc_lead_input" name="name" placeholder="Name" />
                <input id="fcbjc_lead_email" class="fcbjc_lead_input" name="email" type="email" placeholder="Email" />
                <textarea id="fcbjc_lead_message" class="fcbjc_lead_input" name="message" placeholder="Message"></textarea>
                <button id="fcbjc_lead_button" class="fcbjc_lead_button" style="background-color: ' . $background_active_color . ';">Send</button>
            </div>
            <h2 id="fcbjc_success" class="fcbjc_lead_success" style="display: none; color: ' . $text_color . ';">Message sended</h2>
        </div>
        <div id="fcbjc_chat_container" class="fcbjc_chat_container">
            <a href="https://wa.me/' . $whatsapp_number . '" target="_blank">Whatsapp</a>
        </div>
        </div>';
    }
}

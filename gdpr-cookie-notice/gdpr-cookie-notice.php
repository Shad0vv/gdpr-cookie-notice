<?php
/*
Plugin Name: GDPR Cookie Notice
Description: Простой плагин для вывода GDPR-согласия (уведомления о cookies) с настройками в админ-панели.
Version: 1.3
Author: Andrew Arutunyan & Grok
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

class GDPR_Cookie_Notice {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_footer', [$this, 'display_notice']);
        add_action('wp_ajax_gdpr_save_choice', [$this, 'save_user_choice']);
        add_action('wp_ajax_nopriv_gdpr_save_choice', [$this, 'save_user_choice']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function enqueue_scripts() {
        wp_enqueue_style('gdpr-cookie-notice', plugin_dir_url(__FILE__) . 'css/style.css', [], '1.3');
        wp_enqueue_script('gdpr-cookie-notice', plugin_dir_url(__FILE__) . 'js/script.js', ['jquery'], '1.3', true);
        wp_localize_script('gdpr-cookie-notice', 'gdpr_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gdpr_cookie_nonce')
        ]);
    }

public function display_notice() {
    if (!isset($_COOKIE['gdpr_cookie_choice'])) {
        $text = get_option('gdpr_notice_text', 'We use cookies to ensure your best experience on our website. If you continue using our website, we\'ll assume you agree to our policies.');
        $accept_text = get_option('gdpr_accept_text', 'Accept');
        $button_color = get_option('gdpr_button_color', '#007bff');
        $button_hover_color = get_option('gdpr_button_hover_color', '#0056b3');
        $background_color = get_option('gdpr_background_color', '#fff3cd');
        $border_radius = get_option('gdpr_border_radius', 8);
        ?>
        <div id="gdpr-cookie-notice" style="background: <?php echo esc_attr($background_color); ?>; border-radius: <?php echo esc_attr($border_radius); ?>px;">
            <div class="gdpr-content">
		<img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'img/cookie.svg'); ?>" alt="Cookie Icon" class="gdpr-cookie-icon">                <p><?php echo esc_html($text); ?> <a href="<?php echo esc_url(get_privacy_policy_url()); ?>">Policies</a></p>
            </div>
            <div class="gdpr-buttons">
                <button id="gdpr-settings" class="gdpr-settings" title="Settings">
                    <svg class="gdpr-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l-.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </button>
                <button id="gdpr-accept" class="gdpr-button"><?php echo esc_html($accept_text); ?></button>
            </div>
        </div>
        <div id="gdpr-settings-modal" class="gdpr-modal" style="display: none;">
            <div class="gdpr-modal-content">
                <span id="gdpr-modal-close" class="gdpr-modal-close">×</span>
                <h2>Cookie Settings</h2>
                <div class="gdpr-cookie-option">
                    <label>
                        <input type="checkbox" id="gdpr-essential-cookies" checked disabled> Essential Cookies (Always Active)
                    </label>
                    <p>These cookies are necessary for the website to function and cannot be switched off.</p>
                </div>
                <div class="gdpr-cookie-option">
                    <label>
                        <input type="checkbox" id="gdpr-analytics-cookies"> Analytics Cookies
                    </label>
                    <p>These cookies allow us to count visits and traffic sources to improve our website.</p>
                </div>
                <div class="gdpr-cookie-option">
                    <label>
                        <input type="checkbox" id="gdpr-marketing-cookies"> Marketing Cookies
                    </label>
                    <p>These cookies help us show you relevant ads and track their performance.</p>
                </div>
                <div class="gdpr-modal-buttons">
                    <button id="gdpr-save-settings" class="gdpr-button">Save Settings</button>
                </div>
            </div>
        </div>
        <style>
            .gdpr-button {
                background: <?php echo esc_attr($button_color); ?>;
            }
            .gdpr-button:hover {
                background: <?php echo esc_attr($button_hover_color); ?>;
            }
        </style>
        <?php
 	   }
	}

    public function save_user_choice() {
        check_ajax_referer('gdpr_cookie_nonce', 'nonce');
        $choice = isset($_POST['choice']) ? sanitize_text_field($_POST['choice']) : 'decline';
        $analytics = isset($_POST['analytics']) ? sanitize_text_field($_POST['analytics']) : 'false';
        $marketing = isset($_POST['marketing']) ? sanitize_text_field($_POST['marketing']) : 'false';

        $cookie_data = json_encode([
            'choice' => $choice,
            'analytics' => $analytics === 'true',
            'marketing' => $marketing === 'true'
        ]);

        setcookie('gdpr_cookie_choice', $cookie_data, time() + (365 * 24 * 60 * 60), '/');
        wp_send_json_success();
    }

    public function add_admin_menu() {
        add_options_page(
            'Настройки GDPR Cookie Notice',
            'GDPR Cookie Notice',
            'manage_options',
            'gdpr-cookie-notice',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('gdpr_cookie_notice_group', 'gdpr_notice_text', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('gdpr_cookie_notice_group', 'gdpr_accept_text', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('gdpr_cookie_notice_group', 'gdpr_button_color', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('gdpr_cookie_notice_group', 'gdpr_button_hover_color', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('gdpr_cookie_notice_group', 'gdpr_background_color', ['sanitize_callback' => 'sanitize_hex_color']);
        register_setting('gdpr_cookie_notice_group', 'gdpr_border_radius', ['sanitize_callback' => 'absint']);

        add_settings_section(
            'gdpr_main_section',
            'Основные настройки',
            null,
            'gdpr-cookie-notice'
        );

        add_settings_field('gdpr_notice_text', 'Текст уведомления', [$this, 'render_notice_text_field'], 'gdpr-cookie-notice', 'gdpr_main_section');
        add_settings_field('gdpr_accept_text', 'Текст кнопки "Принять"', [$this, 'render_accept_text_field'], 'gdpr-cookie-notice', 'gdpr_main_section');
        add_settings_field('gdpr_button_color', 'Цвет кнопок', [$this, 'render_button_color_field'], 'gdpr-cookie-notice', 'gdpr_main_section');
        add_settings_field('gdpr_button_hover_color', 'Цвет кнопок при наведении', [$this, 'render_button_hover_color_field'], 'gdpr-cookie-notice', 'gdpr_main_section');
        add_settings_field('gdpr_background_color', 'Цвет фона окна', [$this, 'render_background_color_field'], 'gdpr-cookie-notice', 'gdpr_main_section');
        add_settings_field('gdpr_border_radius', 'Радиус закругления (px)', [$this, 'render_border_radius_field'], 'gdpr-cookie-notice', 'gdpr_main_section');
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Настройки GDPR Cookie Notice</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('gdpr_cookie_notice_group');
                do_settings_sections('gdpr-cookie-notice');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_notice_text_field() {
        $value = get_option('gdpr_notice_text', 'We use cookies to ensure your best experience on our website. If you continue using our website, we\'ll assume you agree to our policies.');
        ?>
        <textarea name="gdpr_notice_text" rows="5" cols="50"><?php echo esc_textarea($value); ?></textarea>
        <?php
    }

    public function render_accept_text_field() {
        $value = get_option('gdpr_accept_text', 'Accept');
        ?>
        <input type="text" name="gdpr_accept_text" value="<?php echo esc_attr($value); ?>" />
        <?php
    }

    public function render_button_color_field() {
        $value = get_option('gdpr_button_color', '#007bff');
        ?>
        <input type="text" name="gdpr_button_color" value="<?php echo esc_attr($value); ?>" class="gdpr-color-field" />
        <?php
    }

    public function render_button_hover_color_field() {
        $value = get_option('gdpr_button_hover_color', '#0056b3');
        ?>
        <input type="text" name="gdpr_button_hover_color" value="<?php echo esc_attr($value); ?>" class="gdpr-color-field" />
        <?php
    }

    public function render_background_color_field() {
        $value = get_option('gdpr_background_color', '#fff3cd');
        ?>
        <input type="text" name="gdpr_background_color" value="<?php echo esc_attr($value); ?>" class="gdpr-color-field" />
        <?php
    }

    public function render_border_radius_field() {
        $value = get_option('gdpr_border_radius', 8);
        ?>
        <input type="number" name="gdpr_border_radius" value="<?php echo esc_attr($value); ?>" min="0" />
        <?php
    }
}

new GDPR_Cookie_Notice();

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'settings_page_gdpr-cookie-notice') {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_add_inline_script('wp-color-picker', 'jQuery(document).ready(function($){$(".gdpr-color-field").wpColorPicker();});');
    }
});
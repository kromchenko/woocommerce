<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 29.01.17
 * Time: 23:08
 */
if (!defined('UCALC_PLUGIN')) {
    exit();
}

class uCalcPage
{
    static $headStyle = '';
    static $is_key;

    public function __construct()
    {
        add_action('admin_init', array($this, 'SettingsPage'));
        add_options_page(__('uCalc plugin settings', 'uCalc'), 'uCalc', 'level_10', 'uCalc', array($this, 'ViewsPageSettings'));
    }

    public function settings_header()
    {
        $admin_style = plugins_url('/assets/css/settings.css', dirname(__FILE__));
        wp_enqueue_style('uCalc_settings_css', $admin_style, array(), UCALC_PLUGIN_VER);
        $admin_js = plugins_url('/assets/js/settings.js', dirname(__FILE__));
        wp_enqueue_script('uCalc_settings', $admin_js, array(), UCALC_PLUGIN_VER);
        $this->head_style();
    }

    public function ViewsPageSettings()
    {

        ?>
        <div class="u-wrapper">
            <div class="u-header"></div>
            <div class="u-content">
                <div class="u-content-wrap wp-core-ui">
                    <div id="u-content-text"><?php echo __('To connect a calculator to the website, click on the «Connect account» button and complete the access permission procedure.' , 'uCalc') ?>
                    </div>

                    <form action="options.php" id="setoptions" name="setoptions" method="POST">
                        <!-- START getHash -->
                        <div class="u-hide">
                            <?php
                            do_settings_sections('Ucalc_settings'); // секции с настройками (опциями). У нас она всего одна 'section_id'
                            ?>
                        </div>
                        <input type="button" id="ucalc_get_hash" class="button-primary" onclick="getHash()"
                               value="<?php echo __('Connect account' , 'uCalc') ?>">
                        <input type="button" id="ucalc_del_hash" class="button-primary" onclick="delHash()"
                               value="<?php echo __('Disconnect account' , 'uCalc') ?>">
                        <?php wp_nonce_field('add_uCalc_key', '_wp_nonce'); ?>
                        <iframe id="ucalc_get_hash_iframe_wp" src="https://ucalc.pro/integration-code/wait"
                                style="display: none;"></iframe>
                        <div class="u-hide">
                            <?php
                            settings_fields('option_group');     // скрытые защитные поля
                            submit_button();
                            ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public function SettingsPage()
    {
        $check = uCalc_Core::getCalc();
        self::$is_key = ($check['code'] == 200) ? true : false;
        $this->get_settings();
        register_setting('option_group', 'ucalc_api_key', array($this, 'sanitize_callback'));
        add_settings_section('section_id', __('General settings' , 'uCalc'), '', 'Ucalc_settings');
        add_settings_field('secret_key', __('Enter your KEY' , 'uCalc'), array($this, 'secret_key'), 'Ucalc_settings', 'section_id');
        add_action('admin_head', array($this, 'settings_header'));

    }

    public function get_settings()
    {
        $val = $this->get_key();
        if (esc_attr($val) != "" && self::$is_key) {
            self::$headStyle .= '#ucalc_get_hash, #u-content-text {display: none;}';
        } else {
            self::$headStyle .= '#ucalc_del_hash {display: none;}';
        }
        add_action('wp_head', array($this, 'head_style'));
        if (esc_attr($val) != "" && self::$is_key) {
            self::$headStyle .= '#ucalc_get_hash, #u-content-text {display: none;}';
        } else {
            self::$headStyle .= '#ucalc_del_hash {display: none;}';
        }
    }

    public function get_key()
    {
        $val = get_option('ucalc_api_key');
        if (isset($val['key'])) {
            $val = $val['key'];
        } else {
            $val = '';
        }
        return $val;
    }

    public function secret_key()
    {
        $val = $this->get_key();
        ?>
        <input id="ucalc_hash_wp" type="text" name="ucalc_api_key[key]" value="<?php echo esc_attr($val) ?>"/>
        <?php
    }

    public function head_style()
    {
        if (!empty(self::$headStyle)) {
            echo '<style>' . self::$headStyle . '</style>';
        }
    }

    public function sanitize_callback($options)
    {
        check_admin_referer('add_uCalc_key', '_wp_nonce');
        foreach ($options as $name => & $val) {
            if ($name == 'input')
                $val = esc_attr(strip_tags($val));
        }
        return $options;
    }
}

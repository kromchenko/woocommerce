<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 29.01.17
 * Time: 23:01
 */
if (!defined('UCALC_PLUGIN')) {
    exit();
}

class uCalc_Core
{
    public $page;
    public $MCE;

    public function __construct()
    {
        /** добавление виджета */
        add_action('widgets_init', array($this, 'uCalcWidget'));
        /** добавление ссылки в меню админа на настройки */
        add_action('admin_menu', array($this, 'admin_menu'));
        /** добавление ссылки в параметры в плагинах */
        add_filter('plugin_action_links_' . plugin_basename(UCALC_PLUGIN_NAME . '/ucalc.php'),
            array($this, 'admin_plugin_settings_link'));
        /** добавление шоткодов */
        add_shortcode('uCalc', array($this, 'shortcode'));
        /** добавление в head стили и js */
        add_action('admin_head', array($this, 'uCalc_head'));
        add_action('init', array($this, 'addWidgetBlock'));
        add_action('wp_ajax_ucalc_load_project', array($this, 'ucalc_load_project'));
        add_action('admin_footer', array($this, 'lang'));

        $this->uCalcMCE();

    }

    public function lang()
    {
        echo '
                <script>
                var uCalcLang = {
                    loading: \'' . __('Loading...', 'uCalc') . '\',
                    edit: \'' . __('Manage widget', 'uCalc') . '\',
                    change: \'' . __('Choose another form', 'uCalc') . '\',
                    selector: \'' . __('- Choose the form -', 'uCalc') . '\',
                    select: \'' . __('Choose', 'uCalc') . '\',
                };
                </script>
';
    }

    public function ucalc_load_project()
    {
        $response = [
            'status' => 'ok',
            'message' => '',
            'data' => [],
            'settings_link' => '/wp-admin/options-general.php?page=uCalc',
        ];

        $data = self::getCalc();
        if ($data['code'] === 200) {
            $data = json_decode($data['response']);
            if (count($data) > 0) {
                $response['data'] = $data;
            } else {
                $response['status'] = 'error';
                $response['code'] = 'empty';
                $response['message'] = __('Sorry, you don\'t have any published calculators', 'uCalc');
            }
        } else {
            if (($data === false) || ($data['code'] === 400)) {
                $response['status'] = 'error';
                $response['code'] = 'not_settings';
                $response['message'] = __('Configure the plugin uCalc', 'uCalc');

            } else {
                $response['status'] = 'error';
                $response['code'] = 'key_expired';
                $response['message'] = __('Plugin settings have expired', 'uCalc');
            }
        }
        echo json_encode($response);
        wp_die();
    }

    public function addWidgetBlock()
    {
        if (function_exists('wp_register_script')) {
            wp_register_script(
                'gutenberg-ucalc',
                plugins_url('assets/js/block.js', dirname(__FILE__)),
                array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components')
            );
        }
        if (function_exists('wp_register_style')) {
            wp_register_style(
                'gutenberg-ucalc',
                plugins_url('assets/css/editor.css', dirname(__FILE__)),
                array('wp-edit-blocks'),
                filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/css/editor.css')
            );

            wp_register_style(
                'gutenberg-ucalc',
                plugins_url('assets/css/block.css', dirname(__FILE__)),
                array(),
                filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/css/block.css')
            );
        }

        if (function_exists('register_block_type')) {
            register_block_type('ucalc/block', array(
                'editor_script' => 'gutenberg-ucalc',
                'editor_style' => 'gutenberg-ucalc',
            ));
        }
    }

    public function uCalcWidget()
    {
        register_widget('uCalcWidget');
    }

    public function admin_menu()
    {
        $this->page = new uCalcPage();
    }

    public function uCalcMCE()
    {
        $this->MCE = new uCalcMCE();
    }

    public function admin_plugin_settings_link($links)
    {
        $settings_link = '<a href="options-general.php?page=uCalc">' . __('Parameters', 'uCalc') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function uCalc_head()
    {
        $admin_style = plugins_url('/assets/css/admin_style.css', dirname(__FILE__));
        wp_enqueue_style('uCalc_admin_style', $admin_style, array(), UCALC_PLUGIN_VER);
        ?>
        <script type="text/javascript">
            var ucalc_popup = '/wp-admin/admin-ajax.php?action=ucalc_render_project';
        </script>
        <?php
    }

    public function shortcode($name)
    {
        extract(shortcode_atts(array(
            'id' => ''
        ), $name));
        $calcs = self::getCalc();
        if ($calcs && $calcs['code'] == 200) {
            $respons = json_decode($calcs['response'], true);
            $is_calcs = false;
            foreach ($respons as $calc_info) {
                if ($calc_info['calc_id'] == $name['id']) {
                    $is_calcs = true;
                    break;
                }
            }
            if ($is_calcs) {

                return '<div class="uCalc_' . esc_attr($name['id']) . '"></div><script> var widgetOptions' . esc_attr($name['id']) . ' = { bg_color: "transparent" }; (function() { var a = document.createElement("script"); a.async = true; a.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//ucalc.pro/api/widget.js?id=' . esc_attr($name['id']) . '&t="+Math.floor(new Date()/1800000); document.getElementsByTagName("head")[0].appendChild(a) })();</script>';
            } else {
                return '';
            }
        }

        return '';
    }

    static function getCalc()
    {
        $val = get_option('ucalc_api_key');
        if (isset($val['key'])) {
            $val = $val['key'];
        } else {
            $val = '';
        }
        if (empty($val)) {
            return false;
        }
        $data = array(
            'headers' => array(
                'application/x-www-form-urlencoded'
            ),
            'body' => array(
                'token' => $val)
        );
        $response = wp_remote_post('https://ucalc.pro/account/calculators', $data);
        $code = wp_remote_retrieve_response_code($response);
        $out = wp_remote_retrieve_body($response);
        return array("code" => $code, "response" => $out);
    }
}

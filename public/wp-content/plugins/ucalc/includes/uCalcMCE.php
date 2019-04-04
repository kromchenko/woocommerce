<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 02.03.17
 * Time: 18:11
 */
if (!defined('UCALC_PLUGIN')) {
    exit();
}

class uCalcMCE
{

    public function __construct()
    {
        /** добавление js плагина для mce */
        add_filter('mce_external_plugins', array($this, 'add_mce_plugin'));
        /** Добавление кнопки */
        add_filter('mce_buttons', array($this, 'register_button'));
        /** стили для визуальяного редактора */
        add_filter('mce_css', array($this, 'plugin_mce_css'));
        /** добавляем кнопку для текстового редактора */
        add_action('admin_print_footer_scripts', array($this, 'appthemes_add_quicktags'));
        add_action('wp_ajax_ucalc_render_project', array($this, 'popup_theme'));

    }


    public function popup_theme()
    {
        if (wp_get_current_user()->allcaps['administrator'] === true) {
            $source = "insert";
            $title = __("Your calculators", 'uCalc');
            $insert_text = __("Embed", 'uCalc');
            $res = uCalc_Core::getCalc();
            $minicolors_js = plugins_url('/assets/js/jquery.minicolors.min.js', dirname(__FILE__));
            $minicolors_css = plugins_url('/assets/css/jquery.minicolors.css', dirname(__FILE__));
            $popup_css = plugins_url('/assets/css/popup.css?v4', dirname(__FILE__));
            $popup_js = plugins_url('/assets/js/popup.min.js', dirname(__FILE__));
            require_once UCALC_PLUGIN_DIR . '/assets/views/popup.php';
        }else{
            echo __('Access denied, only admins can view.' , 'uCalc');
        }
        exit();

    }

    public function add_mce_plugin($plugin_array)
    {
        $plugin_array["ucalc_edit_button"] = plugins_url('/assets/js/scripts.min.js', dirname(__FILE__));
        return $plugin_array;
    }

    public function register_button($buttons)
    {
        array_push($buttons, "|", 'ucalc_edit_button');
        return $buttons;
    }

    public function plugin_mce_css($mce_css)
    {
        if (!empty($mce_css))
            $mce_css .= ',';

        $mce_css .= plugins_url('/assets/css/mce_style.css', dirname(__FILE__));

        return $mce_css;
    }

    public function appthemes_add_quicktags()
    {
        if (wp_script_is('quicktags')) {
            ?>
            <script type="text/javascript">
                QTags.addButton('button_uCalc', ' ', '[uCalc id=', ' ]', '', '<?php echo __('Calculator', 'uCalc');?>', 1);

            </script>
            <?php
        }
    }
}
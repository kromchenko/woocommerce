<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 29.01.17
 * Time: 23:35
 */
if (!defined('UCALC_PLUGIN')){
    exit();
}
class uCalcWidget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'ucalc_id_base',
            __('Your calculators', 'uCalc'),
            array('description' => __('uCalc.pro calculator widget', 'uCalc'))
        );
    }


    function widget($args, $instance)
    {
        extract($args);

        //Our variables from the widget settings.

        if (!isset($instance['title'])) {
            $instance['title'] = __('block widget', 'ucalc_widget');
        }

        echo $args['before_widget'];
        $name = $instance['name'];


        //Display the name
        if ($name) {
            $calcs = uCalc_Core::getCalc();
            $is_calcs = false;
            if ($calcs && $calcs['code'] == 200) {
                $response = json_decode($calcs['response'], true);
                foreach ($response as $calc_info) {
                    if ($calc_info['calc_id'] == $name) {
                        $is_calcs = true;
                        break;
                    }
                }
            }
            if ($is_calcs) {
                printf('<div class="uCalc_' . esc_attr($name) .
                    '"></div><script> var widgetOptions' . esc_attr($name) .
                    ' = { bg_color: "transparent" }; (function() { var a = document.createElement("script"); a.async = true; a.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//ucalc.pro/api/widget.js?id=' . esc_attr($name) .
                    '&t="+Math.floor(new Date()/1800000); document.getElementsByTagName("head")[0].appendChild(a) })();</script>');
            } else {
                printf('');
            }
        }


    }

    //Update the widget

    function update($new_instance, $old_instance)
    {

        $instance = array();
        $instance['name'] = (integer)strip_tags($new_instance['name']);
        $instance['title'] = (!empty($new_instance['title'])) ? esc_attr(strip_tags($new_instance['title'])) : '';


        return $instance;
    }


    function form($instance)
    {

        //Set up some default widget settings.
        $defaults = array('name' => __('Calculator ID' , 'uCalc'));
        $instance = wp_parse_args((array)$instance, $defaults); ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('name')); ?>"><?php _e(__('Choose a calculator:' , 'uCalc'), 'example'); ?></label>
            <?php $calcs = uCalc_Core::getCalc();
            $calcs = json_decode($calcs['response'], true);
            if (empty($calcs) || $calcs == 'null') {
                echo __('Sorry, you don\'t have any published calculators' , 'uCalc');
            } elseif (isset($calcs['status'])) {
                echo __('<br>Configure the plugin', 'uCalc');
            } else {
                ?>
            <select name="<?php echo esc_attr($this->get_field_name('name')); ?>"
                    id="<?php echo esc_attr($this->get_field_id('name')); ?> ">
                <option value=""></option>
                <?php
                foreach ($calcs as $calc) {
                    $selected = ($instance['name'] == $calc['calc_id']) ? 'selected' : '';
                    echo '<option value="' . esc_attr($calc['calc_id']) . '" ' . $selected . '>' . esc_attr($calc['calc_name']) . '</option>';
                }
                ?></select><?php
            }

            ?>
        </p>

        <?php
    }
}
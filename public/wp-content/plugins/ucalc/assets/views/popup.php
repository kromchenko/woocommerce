<?php
if (!defined('UCALC_PLUGIN')){
    exit();
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <script type="text/javascript" src="/wp-includes/js/jquery/jquery.js"></script>

    <script>
        // Added tinymce.plugins object to fix bug: #Conversation;id=c41428eb
        (function() {
            var parentWin = function () {
                return (!window.frameElement && window.dialogArguments) || opener || parent || top;
            }();
            parentWin.tinymce.plugins = parentWin.tinymce;
        })();
    </script>
    <script language="javascript" type="text/javascript"
            src="/wp-includes/js/tinymce/tiny_mce_popup.js"></script>

    <script src="<?php echo esc_url($minicolors_js) ?>"></script>
    <link rel="stylesheet" href="<?php echo esc_url($minicolors_css) ?>">
    <link rel="stylesheet" href="<?php echo esc_url($popup_css) ?>">
    <script type="text/javascript" src="<?php echo esc_url($popup_js) ?>"></script>
</head>
<body>

<div id="button-dialog">


    <form action="/" method="get" accept-charset="utf-8">

        <div class="inputrow main" style="padding: 10px">
            <?php if ($res['code'] == 200) {?>
                <label for="button-text"><?php echo  __('Choose a calculator:' , 'uCalc') ?></label>
            <?php } ?>
            <div class="inputwrap">
                <?php
                if ($res['code'] == 200) {
                    $user_calc = json_decode($res['response'], true);
                    if (count($user_calc)>0) { ?>
                        <select id="ucalc_chang_calc">
                            <option></option>
                            <?php

                            foreach ($user_calc as $calc) {
                                echo '<option value="' . esc_attr($calc['calc_id']) . '">' . esc_attr($calc['calc_name']) . '</option>';
                            }
                            ?></select>
                    <?php } else { ?>
                        <p><?php echo  __('Sorry, you don\'t have any published calculators' , 'uCalc')?></p>
                    <?php }
                } 
                else if ($res['code'] == 400) { // не настроен калькулятор
                    echo '<a href="javascript://" onclick="window.parent.location.href=\'/wp-admin/options-general.php?page=uCalc\'" id="insert"
                            style="display: block; line-height: 24px;">'.__('Configure the plugin uCalc' , 'uCalc').'</a>';
                }
                else if ($res['code'] == 401) { // настройки устарели
                    echo '<div style="width: 100%; text-align: center;">'. __('Plugin settings have expired', 'uCalc') .'</div>
                            <a href="javascript://" onclick="window.parent.location.href=\'/wp-admin/options-general.php?page=uCalc\'" id="insert"
                            style="display: block; line-height: 24px;">'. __('Configure the plugin uCalc', 'uCalc').'</a>';
                }
                else {
                    $error = json_decode($res['response'], true);
                    echo $error['error'];
                } ?>
            </div>
            <div class="clear"></div>
        </div>

        <?php if (!empty($user_calc)) { ?>
        <div id="fasc-footer">
            <a href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert"
               style="display: block; line-height: 12px;"><?php echo $insert_text; ?></a>
        </div>
        <?php } ?>


    </form>
</div>

</body>
</html>
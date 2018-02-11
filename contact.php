<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: contact.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__).'/maincore.php';
require_once THEMES."templates/header.php";

$locale = fusion_get_locale('', LOCALE.LOCALESET.'contact.php');
add_to_title($locale['global_200'].$locale['CT_400']);
$settings = fusion_get_settings();

$input = [
    'mailname'     => '',
    'email'        => '',
    'subject'      => '',
    'message'      => '',
    'captcha_code' => '',
];

if (isset($_POST['sendmessage'])) {
    foreach ($input as $key => $value) {
        if (isset($_POST[$key])) {
            // Subject needs 'special' treatment
            if ($key == 'subject') {
                $input['subject'] = substr(str_replace(["\r", "\n", "@"], "", descript(stripslash(trim($_POST['subject'])))), 0, 128); // most unique in the entire CMS. keep.
                $input['subject'] = form_sanitizer($input['subject'], $input[$key], $key);
                // Others don't
            } else {
                $input[$key] = form_sanitizer($_POST[$key], $input[$key], $key);
            }
            // Input not posted, fallback to the default
        } else {
            $input[$key] = form_sanitizer($input[$key], $input[$key], $key);
        }
    }

    if (!iADMIN && $settings['display_validation']) {
        $_CAPTCHA_IS_VALID = FALSE;
        include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php"; // Dynamics need to develop Captcha. Before that, use method 2.
        if (!$_CAPTCHA_IS_VALID) {
            \defender::stop();
            addNotice('warning', $locale['CT_424']);
        }
    }

    if (\defender::safe()) {
        require_once INCLUDES."sendmail_include.php";

        $template_result = dbquery("SELECT template_key, template_active, template_sender_name, template_sender_email FROM ".DB_EMAIL_TEMPLATES." WHERE template_key='CONTACT' LIMIT 1");
        if (dbrows($template_result)) {

            $template_data = dbarray($template_result);
            if ($template_data['template_active'] == "1") {
                if (!sendemail_template("CONTACT", $input['subject'], $input['message'], "", $template_data['template_sender_name'], "", $template_data['template_sender_email'], $input['mailname'], $input['email'])) {
                    \defender::stop();
                    addNotice('danger', $locale['CT_425']);
                }
            } else {
                if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'], $input['message'])) {
                    \defender::stop();
                    addNotice('danger', $locale['CT_425']);
                }
            }

        } else {
            if (!sendemail($settings['siteusername'], $settings['siteemail'], $input['mailname'], $input['email'], $input['subject'], $input['message'])) {
                \defender::stop();
                addNotice('danger', $locale['CT_425']);
            }
        }

        if (\defender::safe()) {
            addNotice('success', $locale['CT_440']);
            redirect(BASEDIR.'contact.php');
        }
    }
}

opentable($locale['CT_400']);
$message = str_replace("[SITE_EMAIL]", hide_email(fusion_get_settings('siteemail')), $locale['CT_401']);
$message = str_replace("[PM_LINK]", "<a href='messages.php?msg_send=1'>".$locale['global_121']."</a>", $message);
echo "<div class='text-center well'>".$message."</div>\n";
echo "<!--contact_pre_idx-->";
echo openform('contactform', 'post', FORM_REQUEST);
echo form_text('mailname', $locale['CT_402'], $input['mailname'], ['required' => TRUE, 'error_text' => $locale['CT_420'], 'max_length' => 64]);
echo form_text('email', $locale['CT_403'], $input['email'], ['required' => TRUE, 'error_text' => $locale['CT_421'], 'type' => 'email', 'max_length' => 64]);
echo form_text('subject', $locale['CT_404'], $input['subject'], ['required' => TRUE, 'error_text' => $locale['CT_422'], 'max_length' => 64]);
echo form_textarea('message', $locale['CT_405'], $input['message'], ['required' => TRUE, 'error_text' => $locale['CT_423'], 'max_length' => 128]);

if (iGUEST) {
    echo '<div class="row">';
    echo '<div class="col-xs-12 col-sm-8 col-md-6">';
    include INCLUDES.'captchas/'.$settings['captcha'].'/captcha_display.php';
    echo '</div>';
    echo '<div class="col-xs-12 col-sm-4 col-md-6">';

    if (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT)) {
        echo form_text('captcha_code', $locale['CT_408'], '', ['required' => TRUE, 'autocomplete_off' => TRUE, 'input_id' => 'contact-captcha_code']);
    }

    echo '</div>';
    echo '</div>';
}

echo form_button('sendmessage', $locale['CT_406'], $locale['CT_406'], ['class' => 'btn-primary', 'icon' => 'fa fa-send-o']);
echo closeform();
echo "<!--contact_sub_idx-->";
closetable();
require_once THEMES."templates/footer.php";

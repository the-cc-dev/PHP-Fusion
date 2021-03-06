<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: icq_bbcode_include.php
| Author: Wooya
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
$text = preg_replace('#\[icq\]([0-9]*?)\[/icq\]#si',
    '<strong>'.$locale['bb_icq'].'</strong> <a href=\'http://www.icq.com/people/webmsg.php?to=\1\' target=\'_blank\'><img src=\'http://status.icq.com/online.gif?img=27&amp;icq=\1\' alt=\'\1\' style=\'vertical-align:middle;border:none;\'></a><a href=\'icq:send_message?uin=\1\' target=\'_blank\'>\1</a>',
    $text);

<?php
/*
PLUGIN NAME: wp-PushMessenger
PLUGIN URI: http://ipush.me/?page_id=48
DESCRIPTION: A plugin for interfacing your Wordpress with Push Messenger, an application for receiving custom push notifications on your iPhone. You never need check your blog manually again and again just looking for new comment/trackback. When new comment/trackback on your blog available, this plugin will send you a iPhone push notification about that.
AUTHOR: since2006
AUTHOR URI: http://ipush.me/
VERSION: 0.1
*/

/*
    Copyright 2009 Nathan Wittstock (email: nate at milkandtang dot com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
global $wpPM_options;
global $wpPM_curl_present;
global $wpPM_user_id;
global $wpPM_secret_token;
global $wpPM_versionCode;
global $wpPM_version;

require_once "PushMessenger.php";

function wpPushMessenger_init_translation()
{
    load_plugin_textdomain('wpPushMessengerTD', PLUGINDIR . '/wp-PushMessenger/translation');
}

function wpPushMessenger()
{
    global $wpPM_version;
    global $wpPM_versionCode;
    global $wpPM_curl_present;
    global $wpPM_user_id;
    global $wpPM_secret_token;

    add_action('init', 'wpPushMessenger_init_translation');

    add_action('admin_menu', 'wpPushMessenger_menu');

    add_action('comment_post', 'wpPushMessenger_comment', 99);

    // add / remove settings on activation/deactivation
    register_deactivation_hook(__FILE__, 'wpPushMessenger_deactivation');
    register_activation_hook(__FILE__, 'wpPushMessenger_activation');

    $wpPM_version = "1.0.0";
    $wpPM_versionCode = "1";

    $wpPM_curl_present = wpPushMessenger_checkCurl();

    $wpPM_user_id = get_option("wpPushMessenger_userid");
    $wpPM_secret_token = get_option("wpPushMessenger_secrettoken");

    $stored_version = get_option('wpPushMessenger_version');
    if ($stored_version != $wpPM_versionCode) { //need to run an upgrade
        update_option('wpPushMessenger_version', $wpPM_versionCode);
    }
}

function wpPushMessenger_menu()
{
    add_options_page(__('wp-PushMessenger Configuration', 'wpPushMessengerTD'), 'wp-PushMessenger', 8, 'wp-PushMessenger', 'wpPushMessenger_options_panel');
}

function wpPushMessenger_activation()
{
    global $wpPM_versionCode;

    add_option('wpPushMessenger_userid', '');
    add_option('wpPushMessenger_secrettoken', '');
    add_option('wpPushMessenger_oncomment', 'yes');
    add_option('wpPushMessenger_commentformat', __('From: %a\nRe: %t\n%c', 'wpPushMessengerTD'));
    add_option('wpPushMessenger_commentsendspam', '1');
    add_option('wpPushMessenger_ontrackback', 'yes');
    add_option('wpPushMessenger_trackbackformat', __('From: %a\nRe: %t\n%c', 'wpPushMessengerTD'));
    add_option('wpPushMessenger_version', $wpPM_versionCode);
}

function wpPushMessenger_deactivation()
{
    delete_option('wpPushMessenger_userid');
    delete_option('wpPushMessenger_secrettoken');
    delete_option('wpPushMessenger_oncomment');
    delete_option('wpPushMessenger_commentformat');
    delete_option('wpPushMessenger_commentsendspam');
    delete_option('wpPushMessenger_ontrackback');
    delete_option('wpPushMessenger_trackbackformat');
    delete_option('wpPushMessenger_version');
}

function wpPushMessenger_options_panel()
{
    //echo get_option('wpPushMessenger_commentformat');;
    global $wpPM_version;
    global $wpPM_versionCode;
    global $wpPM_curl_present;
    global $wpPM_user_id;
    global $wpPM_secret_token;

    if (($_GET['updated'] == "true" || $_GET["settings-updated"] == "true") && !empty($wpPM_user_id) && !empty($wpPM_secret_token)) {
        $verification = "";
        $error = false;

        $pm = new PushMessenger($wpPM_user_id, $wpPM_secret_token);

        if ($pm->verify())
            $verification .= sprintf(__('User ID: %d, Secret Token: %s Verified Successfully!', 'wpPushMessengerTD'), $wpPM_user_id, $wpPM_secret_token) . '<br/>';
        else {
            $verification .= sprintf(__('User ID: %d, Secret Token: %s <strong>DID NOT</strong> verify successfully...', 'wpPushMessengerTD'), $wpPM_user_id, $wpPM_secret_token) . '<br/>';
            $error = true;
        }
        $updated = "updated";
        if ($error) $updated = "error";
        echo "<div class=\"$updated\"><strong>" . __('API Key Status', 'wpPushMessengerTD') . ":</strong><br/>$verification</div>";
    }

    require_once "options_panel.php";
}

function wpPushMessenger_checkCurl()
{
    if (!function_exists('curl_exec')) {
        return false;
    }
    return true;
}

function wpPushMessenger_comment($comment_id)
{
    global $wpPM_options;
    global $recaptcha_saved_error;

    global $wpPM_user_id;
    global $wpPM_secret_token;

    $wpPM_user_id = get_option("wpPushMessenger_userid");
    $wpPM_secret_token = get_option("wpPushMessenger_secrettoken");

    if (empty($wpPM_user_id) || empty($wpPM_secret_token)) {
        return;
    }

    if ($recaptcha_saved_error) return; //ignore failed reCAPTCHA comments

    $comment = get_comment($comment_id);
    $post = get_post($comment->comment_post_ID);

    $approval = $comment->comment_approved;
    if (strpos($comment->comment_approved, 'spam') === 0) {
        $approval = 2;
    }

    switch ($approval) {
        case 0:
            $approval = __('Pending', 'wpPushMessengerTD');
            $comment->is_approved = 0;
            break;
        case 1:
            $approval = __('Approved', 'wpPushMessengerTD');
            $comment->is_approved = 2;
            break;
        case 2:
            $approval = __('Spam', 'wpPushMessengerTD');
            $comment->is_approved = 1;
            break;
        default:
            $approval = __('Status Unknown', 'wpPushMessengerTD');
            $comment->is_approved = 3;
            break;
    }

    //Send all notifications: 2
    //Send all, but not spam: 1
    //Send all, but not unapproved and spam: 0

    $spam = (int)get_option('wpPushMessenger_commentsendspam');
    if ($spam == 1 && $comment->is_approved == 1) return;
    if ($spam == 0 && $comment->is_approved < 2) return;

    $search = array(
        '%a',
        '%e',
        '%t',
        '%c',
        '%s',
        '%n',
        '%i',
        '%p',
        '%u');
    $replace = array(
        $comment->comment_author,
        $comment->comment_author_email,
        wpPushMessenger_thumb($post->post_title, 8),
        $comment->comment_content,
        $comment->comment_author_url,
        $post->comment_count,
        $comment->comment_author_IP,
        $approval,
        get_permalink($post->ID));

    if ($comment->comment_type == 'trackback' || $comment->comment_type == 'pingback') {
        if (get_option('wpPushMessenger_ontrackback') != 'yes') return;
        $format = get_option('wpPushMessenger_trackbackformat');
        if (empty($format)) {
            $format = __("From: %a\nRe: %t\n%c", 'wpPushMessengerTD');
        } else {
            $format = str_replace('\n', "\n", $format);
        }

        $msg = new PushMessage();
        $msg->setApplication("WordPress");
        $msg->setTitle(__('New Ping/Trackback', 'wpPushMessengerTD'));
        $msg->setContent(wpPushMessenger_cleanupmsg(str_replace($search, $replace, $format)));

        $pusher = new PushMessenger($wpPM_user_id, $wpPM_secret_token);
        $pusher->push($msg);
    } else {
        if (get_option('wpPushMessenger_oncomment') != 'yes') return;
        $format = get_option('wpPushMessenger_commentformat');
        if (empty($format)) {
            $format = __("From: %a\nRe: %t\n%c", 'wpPushMessengerTD');
        } else {
            $format = str_replace('\n', "\n", $format);
        }

        $msg = new PushMessage();
        $msg->setApplication("WordPress");
        $msg->setTitle(__('New Comment', 'wpPushMessengerTD'));
        $msg->setContent(wpPushMessenger_cleanupmsg(str_replace($search, $replace, $format)));

        $pusher = new PushMessenger($wpPM_user_id, $wpPM_secret_token);
        $pusher->push($msg);
    }
}

function wpPushMessenger_thumb($str, $length = 0)
{
    if ($length == 0) {
        return $str;
    }

    $returnstr = "";
    $i = 0;
    $n = 0;

    $str_length = strlen($str);
    while (($n < $length) and ($i <= $str_length))
    {
        $temp_str = substr($str, $i, 1);
        $ascnum = ord($temp_str);
        if ($ascnum >= 224)
        {
            $returnstr = $returnstr . substr($str, $i, 3);
            $i = $i + 3;
            $n++;
        }
        elseif ($ascnum >= 192)
        {
            $returnstr = $returnstr . substr($str, $i, 2);
            $i = $i + 2;
            $n++;
        }
        elseif ($ascnum >= 65 && $ascnum <= 90)
        {
            $returnstr = $returnstr . substr($str, $i, 1);
            $i = $i + 1;
            $n++;
        } else
        {
            $returnstr = $returnstr . substr($str, $i, 1);
            $i = $i + 1;
            $n = $n + 0.5;
        }
    }
    if ($str_length > strlen($returnstr)) {
        $returnstr = $returnstr . "...";
    }
    return $returnstr;
}

function wpPushMessenger_cleanupmsg($string)
{
    return strip_tags(str_replace("\r", "\n", str_replace("\r\n", "\n", $string)));
}

wpPushMessenger();
?>

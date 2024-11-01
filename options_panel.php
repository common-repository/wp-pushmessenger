<link rel="stylesheet" type="text/css" href="../<?php echo PLUGINDIR;?>/wp-PushMessenger/style.css"/>
<div class="wrap">
    <h2><?php _e('wp-PushMessenger Configuration', 'wpPushMessengerTD'); ?></h2>
    <?php
        if (!$wpPM_curl_present) {
    ?>
    <div class="error">
        <b><?php _e('WARNING', 'wpPushMessengerTD'); ?>:</b>

        <p><?php _e('There\'s a problem with your webserver configuration that will stop Push Messenger from functioning.<br/>The cURL library is missing vital functions. cURL is required to execute Push Message.<br/>You\'ll need to enable cURL support on your webserver. Speak to your hosting provider if this is confusing.', 'wpPushMessengerTD'); ?></p>
    </div>
    <?php
        }
    ?>

    <div id="wpp_donate">
        
    </div>

    <?php _e('A plugin for interfacing your Wordpress with Push Messenger, an application for receiving custom push notifications on your iPhone.<p/> You never need check your blog manually again and again just looking for new comment/trackback. When new comment/trackback on your blog available, this plugin will send you a iPhone push notification about that.', 'wpPushMessengerTD'); ?>

    <hr/>

    <form method="post" action="./options.php">

        <?php wp_nonce_field('update-options'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('User ID:', 'wpPushMessengerTD'); ?></th>
                <td>
                    <input size="40" type="text" name="wpPushMessenger_userid" value="<?php echo $wpPM_user_id; ?>"/>
                    <br/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Secret Token:', 'wpPushMessengerTD'); ?></th>
                <td>
                    <input size="40" type="text" name="wpPushMessenger_secrettoken" value="<?php echo $wpPM_secret_token; ?>"/>
                    <br/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo _e('Notifications:', 'wpPushMessengerTD'); ?>
                    <div class="wpp_key">
                        <h4><?php _e('All Types', 'wpPushMessengerTD'); ?></h4>
                        <ul>
                            <li>%a = <?php _e('Author', 'wpPushMessengerTD'); ?></li>
                            <li>%e = <?php _e('Author Email', 'wpPushMessengerTD'); ?></li>
                            <li>%t = <?php _e('Post Title', 'wpPushMessengerTD'); ?></li>
                            <li>%u = <?php _e('Link to Post', 'wpPushMessengerTD'); ?></li>
                            <li>\n = <?php _e('Line break', 'wpPushMessengerTD'); ?></li>
                        </ul>
                        <h4><?php _e('Comments', 'wpPushMessengerTD'); ?></h4>
                        <ul>
                            <li>%c = <?php _e('Comment Content', 'wpPushMessengerTD'); ?></li>
                            <li>%p = <?php _e('Approval Status', 'wpPushMessengerTD'); ?></li>
                        </ul>
                        <h4><?php _e('Comments/Trackbacks', 'wpPushMessengerTD'); ?></h4>
                        <ul>
                            <li>%s = <?php _e('Author URL', 'wpPushMessengerTD'); ?></li>
                            <li>%n = <?php _e('Number of Comments', 'wpPushMessengerTD'); ?></li>
                            <li>%i = <?php _e('Author IP', 'wpPushMessengerTD'); ?></li>
                        </ul>
                    </div>
                </th>
                <td>
                    <table class="wpp_table">
                        <tr>
                            <th scope="col">&nbsp;</th>
                            <th scope="col"><?php _e('On', 'wpPushMessengerTD'); ?></th>
                            <th scope="col"><?php _e('Format', 'wpPushMessengerTD'); ?></th>
                        </tr>
                        <tr class="wpp_odd">
                            <th scope="row"><?php _e('Comments', 'wpPushMessengerTD'); ?></th>
                            <td>
                                <input type="checkbox" name="wpPushMessenger_oncomment"
                                       value="yes"
                                        <?php if (get_option('wpPushMessenger_oncomment') == "yes") { echo 'checked="checked"'; }?> />
                            </td>
                            <td>
                                <input size="30" type="text" name="wpPushMessenger_commentformat"
                                       value="<?php echo get_option('wpPushMessenger_commentformat'); ?>"/>
                            </td>
                        </tr>
                        <tr class="wpp_even">
                            <th scope="row"><?php _e('Trackback/Pings', 'wpPushMessengerTD'); ?></th>
                            <td>
                                <input type="checkbox" name="wpPushMessenger_ontrackback"
                                       value="yes"
                                        <?php if (get_option('wpPushMessenger_ontrackback') == "yes") { echo 'checked="checked"'; }?> />
                            </td>
                            <td>
                                <input size="30" type="text" name="wpPushMessenger_trackbackformat"
                                       value="<?php echo get_option('wpPushMessenger_trackbackformat'); ?>"/>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Spam Comments:', 'wpPushMessengerTD'); ?></th>
                <td>
                    <?php $spam = get_option('wpPushMessenger_commentsendspam'); ?>
                    <input type="radio" name="wpPushMessenger_commentsendspam" value="2" <?php if ($spam == "2") {
                        echo 'checked="checked"';
                    }?> />
                    <?php _e('Send Notifications for all comments', 'wpPushMessengerTD'); ?><br/>
                    <input type="radio" name="wpPushMessenger_commentsendspam" value="1" <?php if ($spam == "1") {
                        echo 'checked="checked"';
                    }?> />
                    <?php _e('Ignore &ldquo;Spam&rdquo; Comments, but not &ldquo;Unapproved&rdquo; Comments', 'wpPushMessengerTD'); ?>
                    <br/>
                    <input type="radio" name="wpPushMessenger_commentsendspam" value="0" <?php if ($spam == "0") {
                        echo 'checked="checked"';
                    }?> />
                    <?php _e('Ignore &ldquo;Spam&rdquo; and &ldquo;Unapproved&rdquo; Comments', 'wpPushMessengerTD'); ?>
                </td>
            </tr>
        </table>

        <input type="hidden" name="action" value="update"/>
        <input type="hidden" name="page_options"
               value="wpPushMessenger_userid,wpPushMessenger_secrettoken,wpPushMessenger_oncomment,wpPushMessenger_commentformat,wpPushMessenger_commentsendspam,wpPushMessenger_ontrackback,wpPushMessenger_trackbackformat"/>

        <p class="submit">
            <input type="submit" class="button-primary" value="Update Settings"/>
        </p>

    </form>
    <div id="wpp_footer">wp-PushMessenger v<?php echo $wpPM_version; ?> &copy;2011 based on: <a
            href="http://milkandtang.com">milkandtang</a>. <?php _e('Released under the <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU General Public Licence</a>. Huzzah.', 'wpPushMessengerTD'); ?>
    </div>
</div>

<?php

/*
This script creates a new Wordpress account with default data (user: superuser, pass: superuser).
Install in it in your wordpress installation directory i.e. where the wp-config.php resides.
Start your browser and access: yoursite/wordpress-install/wp-uc.php
This is useful when the automated process has failed for some reason to create the accounts.
You must immediately login and change the password of the newly created user.
After that please delete this file (wp-uc.php) to avoid any problems.

Author: Svetoslav Marinov <slavi@slavi.biz>
Donation Link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CU8C9NF38QAKA
Download: https://github.com/lordspace/Wordpress-User-Creator/zipball/master
Credits: ziggysdaydream http://wordpress.org/support/topic/how-to-create-an-admin-manually-solution-to-step-2-installation-freeze
Blog: http://devcha.com
Main Site: http://WebWeb.ca
Version: 1.0, License: LGPL, Disclaimer: Use it at your own risk! Delete the file after usage.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

$instructions = file_get_contents(__FILE__);
$instructions = substr($instructions, 0, 1500);
$instructions = preg_replace('#.*\/\*\s*(.*)\*\/.*#si', '\\1', $instructions); // get instructions from the comment above. smart, eh ?
$instructions = str_replace( array('<', '>'), array('&lt;', '&gt;'), $instructions); // fix any emails
$instructions = preg_replace( "#((http|ftp)+(s)?:\/\/[^<>\s]+)#si", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $instructions); // clickable links

$user = 'superuser';
$pass = '$P$BV97Q40xVJlYTu3yQBEn41OA0L5nbo/';

$output = '';

if (!empty($_POST)) {
    $output .= "<h3>Result:</h3>";
    $res = @include_once(dirname(__FILE__) .  DIRECTORY_SEPARATOR . 'wp-config.php');
    $errors = $success = array();
    
    if (empty($res)) {
        $errors[] = "Cannot load 'wp-config.php'";
    }
    
    if (!defined('DB_NAME')) {
        $errors[] = "DB_NAME constant not found.";
    }
    
    if (defined('DB_NAME')) {
        $db_conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
        $res = mysql_select_db(DB_NAME);
           
        if (empty($db_conn) || empty($res)) {
            $errors[] = "Cannot connect to db OR select the Db: " . DB_NAME;
        }
    }
    
    /*
        Plan.
        Source: http://wordpress.org/support/topic/how-to-create-an-admin-manually-solution-to-step-2-installation-freeze
        
        1) in WP_USERS enter the following values for the following fields:
        user_login: admin
        user_pass: [anything]
        user_email: [your email]
        user_registered: [the date]
        display_name: [a display name]
        user_nicename: [a nicname]
        
        2) in the WP_USERMETA table enter the following values for the follwoing fields:
        
        user_id: 1
        meta_key: wp_user_level
        meta_value: 10
        
        In another row in the same table enter these values:
        
        user_id: 1
        meta_key: wp_capabilities
        meta_value: "a:1:{s:13:"administrator";b:1;}" (without the quotes)
     */
    
    if (empty($errors)) {
        // check if such user already exists.
        $result = mysql_query("SELECT * FROM " . $table_prefix . 'users WHERE user_login = "' . mysql_real_escape_string($user) . '"');
        
        @$row = mysql_fetch_array($result);
        
        // The use hasn't been added yet
        if (!empty($result) && empty($row)) {
            $vals = '';
            $vals .= '  user_login = "' . mysql_real_escape_string($user) . '"';
            $vals .= ', user_pass  = "' . mysql_real_escape_string($pass) . '"';
            $vals .= ', display_name = "' . mysql_real_escape_string($user) . '"';
            $vals .= ', user_nicename = "' . mysql_real_escape_string($user) . '"';
            $vals .= ', user_email  = "' . mysql_real_escape_string('change@me.com') . '"';
            $vals .= ', user_registered  = NOW()';
            $vals .= ', user_status   = 0'; // WP has it this way.
            
            $res = mysql_query("INSERT INTO {$table_prefix}users SET
            	$vals
            ");
            	
        	if (empty($res)) {
        	    $errors[] = "Cannot create a user " . $user;
        	}

            $user_id = mysql_insert_id();
            
            $res1 = mysql_query("INSERT INTO {$table_prefix}usermeta SET "
                . "user_id = $user_id, meta_key = '{$table_prefix}user_level', meta_value = 10");

            $res2 = mysql_query("INSERT INTO {$table_prefix}usermeta SET user_id = $user_id,
        			meta_key = '{$table_prefix}capabilities', meta_value = '" . mysql_real_escape_string('a:1:{s:13:"administrator";b:1;}') . "'");

            if (!empty($user_id) && !empty($res1) && !empty($res2)) {
                $success[] = "Successfully created a new admin user. User: {$user} User ID: " . $user_id;
                $success[] = "Next: <a href='wp-login.php'>Login Now</a>";
            } else {
                $errors[] = mysql_error();
            }
        } else {
            $errors[] = "The user user '" . $user . '\' already exists.';
        }
        
        if (!empty($errors)) {
            $output .= "<span class='error'> " . join("<br/>\n", (array) $errors) . ".</span>";
        }
        
        if (!empty($success)) {
            $output .= "<span class='success'> " . join("<br/>\n", (array) $success) . ".</span>";
        }
    }
    
    $output .= "<br/>";
}
?>
<html>
    <head>
        <title>Wordpress User Creator</title>
	    <meta name="keywords" content="" />
	    <meta name="description" content="" />
        <meta name="MSSmartTagsPreventParsing" content="true">
        <meta HTTP-EQUIV="content-type" content="text/html; charset=utf-8">
        <link rel="stylesheet" href="fbapi.style.css" type="text/css" media="screen" />
        <style>
            html {
                height: 100%;
                padding-bottom: 1px; /* force scrollbars */
            }

            body {
                background: #FFF;
                color: #444;
                font: normal 100% sans-serif;
                line-height: 1.5;
            }

            label {
                width: 150px;
            }

            #pp_form {
                display:inline;
                vertical-align:top;
            }

            input[type=text] {
                width: 320px;
            }

            .success {
                color: green;
            }

            .error {
                color: red;
            }
        </style>
    </head>
<body>
<div id="site-wrapper">

<h2>Wordpress User Creator</h2>
<p>
<?php echo nl2br($instructions); ?>
</p>
<div class="search">
    <form method="post" action="">
        <table width="50%">
        <tr>
            <td colspan="2">
                <input type="submit" name="proceed" value="Proceed"/>
            </td>
        </tr>
        </table>
    </form>
</div>

    <div>
        <?php echo $output;?>
    </div>

    <div id="footer">
        <div class="clearer">&nbsp;</div>
        <div id="footer-right" class="right">
            (c) Svetoslav Marinov  <strong>&lt;slavi@slavi.biz&gt;</strong> <span class="text-separator">|</span>
       
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="pp_form" target="_blank">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="CU8C9NF38QAKA">
                <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
            
            <span class="text-separator">|</span>
            <a target="_blank" href="http://devcha.com">My Blog</a> <span class="text-separator">|</span>
            <a target="_blank" href="http://webweb.ca">http://WebWeb.ca</a>
        </div>
    </div>

</div>

</body>
</html>
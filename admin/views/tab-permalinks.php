<?php
/**
 * @package YMBESEO\Admin
 */

if ( ! defined( 'YMBESEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$yform = Yoast_Form::get_instance();
$yform->currentoption = 'ymbeseo_permalinks';

echo '<h3>', __( 'Change URLs', 'ymbeseo' ), '</h3>';
$yform->checkbox( 'stripcategorybase', __( 'Strip the category base (usually <code>/category/</code>) from the category URL.', 'ymbeseo' ) );

echo '<p>' . __( 'Attachments to posts are stored in the database as posts, this means they\'re accessible under their own URL\'s if you do not redirect them, enabling this will redirect them to the post they were attached to.', 'ymbeseo' ) . '</p>';
$yform->checkbox( 'redirectattachment', __( 'Redirect attachment URL\'s to parent post URL.', 'ymbeseo' ) );

echo '<h3>', __( 'Clean up permalinks', 'ymbeseo' ), '</h3>';
echo '<p>' . __( 'This helps you to create cleaner URLs by automatically removing the stopwords from them.', 'ymbeseo' ) . '</p>';
$yform->checkbox( 'cleanslugs', __( 'Remove stop words from slugs.', 'ymbeseo' ) );

echo '<p>' . __( 'This prevents threaded replies from working when the user has JavaScript disabled, but on a large site can mean a <em>huge</em> improvement in crawl efficiency for search engines when you have a lot of comments.', 'ymbeseo' ) . '</p>';
$yform->checkbox( 'cleanreplytocom', __( 'Remove the <code>?replytocom</code> variables.', 'ymbeseo' ) );

/* translators %s expands to <code>.html</code> */
echo '<p>' . sprintf( __( 'If you choose a permalink for your posts with %1$s, or anything else but a %2$s at the end, this will force WordPress to add a trailing slash to non-post pages nonetheless.', 'ymbeseo' ), '<code>.html</code>', '<code>/</code>' ) . '</p>';
$yform->checkbox( 'trailingslash', __( 'Enforce a trailing slash on all category and tag URL\'s', 'ymbeseo' ) );

echo '<p>' . __( 'People make mistakes in their links towards you sometimes, or unwanted parameters are added to the end of your URLs, this allows you to redirect them all away. Please note that while this is a feature that is actively maintained, it is known to break several plugins, and should for that reason be the first feature you disable when you encounter issues after installing this plugin.', 'ymbeseo' ) . '</p>';
$yform->checkbox( 'cleanpermalinks', __( 'Redirect ugly URL\'s to clean permalinks. (Not recommended in many cases!)', 'ymbeseo' ) );

echo '<div id="cleanpermalinksdiv">';
echo '<p>' . __( 'Google Site Search URL\'s look weird, and ugly, but if you\'re using Google Site Search, you probably do not want them cleaned out.', 'ymbeseo' ) . '</p>';
$yform->checkbox( 'cleanpermalink-googlesitesearch', __( 'Prevent cleaning out Google Site Search URL\'s.', 'ymbeseo' ) );

/* translators %s expands to <code>?utm_</code> */
echo '<p>' . sprintf( __( 'If you use Google Analytics campaign parameters starting with %s, check this box. However, you\'re advised not to use these. Instead, use the version with a hash.', 'ymbeseo' ), '<code>?utm_</code>' ) . '</p>';
$yform->checkbox( 'cleanpermalink-googlecampaign', __( 'Prevent cleaning out Google Analytics Campaign & Google AdWords Parameters.', 'ymbeseo' ) );

echo '<p>' . __( 'You might have extra variables you want to prevent from cleaning out, add them here, comma separated.', 'ymbeseo' ) . '</p>';
$yform->textinput( 'cleanpermalink-extravars', __( 'Other variables not to clean', 'ymbeseo' ) );
echo '</div>';

/* translators %s expands to <code>&lt;head&gt;</code> */
echo '<h3>', sprintf( __( 'Clean up the %s', 'ymbeseo' ), '<code>&lt;head&gt;</code>' ), '</h3>';
$yform->checkbox( 'hide-rsdlink', __( 'Hide RSD Links', 'ymbeseo' ) );
$yform->checkbox( 'hide-wlwmanifest', __( 'Hide WLW Manifest Links', 'ymbeseo' ) );
$yform->checkbox( 'hide-shortlink', __( 'Hide Shortlink for posts', 'ymbeseo' ) );
$yform->checkbox( 'hide-feedlinks', __( 'Hide RSS Links', 'ymbeseo' ) );

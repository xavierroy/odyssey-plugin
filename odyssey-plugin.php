<?php

/**
 *
 * @link              https://xavierroy.com
 * @since             1.0.0
 * @package           Odyssey_Plugin.php
 *
 * @wordpress-plugin
 * Plugin Name:       Odyssey - Site Enhancements
 * Plugin URI:        https://github.com/xavierroy/odyssey-plugin/
 * Description:       Tweaks and hacks for this site...
 * Version:           1.0.14.2
 * Author:            Xavier Roy
 * Author URI:        https://xavierroy.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       odyssey-plugin.php
 * Domain Path:       /languages
 * GitHub Plugin URI:	xavierroy/odyssey-plugin
 * GitHub Plugin URI:	https://github.com/xavierroy/odyssey-plugin

 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
/*
 * Table of Contents
 * 1. Shortcode for Search Form
 * 2. WordPress filter to approve webmentions from previously-approved domains
 * 3. Add categories and tags from slug
 * 4. Replace blank titles with timestamps
 * 5. Disable Self Pingbacks
 * 6. Disable Gutenberg
 * 7. Add emojis
 * 8. Identify Post kinds in Feeds
*/

/*
1. Shortcode for Search Form
The [wpsearch] shortcode will add a search form anywhere in a post or page.
*/
add_shortcode('wpsearch', 'get_search_form');
/* --1-- */


/*
2. WordPress filter to approve webmentions from previously-approved domains
Source: https://gist.github.com/gRegorLove/8215cb9c9584b364aaf4ef2999416f56
*/
if ( !function_exists('indieweb_check_webmention') ) {

	/**
	 * Using the webmention_source_url, approve webmentions that have been received from previously-
	 * approved domains. For example, once you approve a webmention from http://example.com/post,
	 * future webmentions from http://example.com will be automatically approved.
	 * Recommend placing in your theme's functions.php
	 *
	 * Based on check_comment()
	 * @see https://core.trac.wordpress.org/browser/tags/4.9/src/wp-includes/comment.php#L113
	 */
	function indieweb_check_webmention($approved, $commentdata) {
		global $wpdb;

		if ( 1 == get_option('comment_whitelist')) {

			if ( !empty($commentdata['comment_meta']['webmention_source_url']) ) {
				$like_domain = sprintf('%s://%s%%', parse_url($commentdata['comment_meta']['webmention_source_url'], PHP_URL_SCHEME), parse_url($commentdata['comment_meta']['webmention_source_url'], PHP_URL_HOST));

				$ok_to_comment = $wpdb->get_var( $wpdb->prepare( "SELECT comment_approved FROM $wpdb->comments WHERE comment_author = %s AND comment_author_url LIKE %s AND comment_approved = '1' LIMIT 1", $commentdata['comment_author'], $like_domain ) );

				if ( 1 == $ok_to_comment ) {
					return 1;
				}

			}

		}

		return $approved;
	}

	add_filter('pre_comment_approved', 'indieweb_check_webmention', '99', 2);

}
/* --2-- */


/*
3. Set default category / tags for a new WordPress posts when using bookmarklets
Source: https://gist.github.com/davejamesmiller/1966543
*/


add_filter('wp_get_object_terms', function($terms, $object_ids, $taxonomies, $args)
{
    if (!$terms && basename($_SERVER['PHP_SELF']) == 'post-new.php') {
        // Category - note: only 1 category is supported currently
        if ($taxonomies == "'category'" && isset($_REQUEST['category'])) {
            $id = get_cat_id($_REQUEST['category']);
            if ($id) {
                return array($id);
            }
        }
        // Tags
        if ($taxonomies == "'post_tag'" && isset($_REQUEST['tags'])) {
            $tags = $_REQUEST['tags'];
            $tags = is_array($tags) ? $tags : explode( ',', trim($tags, " \n\t\r\0\x0B,") );
            $term_ids = array();
            foreach ($tags as $term) {
                if ( !$term_info = term_exists($term, 'post_tag') ) {
                    // Skip if a non-existent term ID is passed.
                    if ( is_int($term) )
                        continue;
                    $term_info = wp_insert_term($term, 'post_tag');
                }
                $term_ids[] = $term_info['term_id'];
            }
            return $term_ids;
        }
    }
    return $terms;
}, 10, 4);

/* ---3--- */

/*
4. Replace blank titles with timestamps
Source: https://github.com/colin-walker/wordpress-blank-title
*/
function filter_title_save_pre( $title ) {
    if ( $title == "" ) {
      date_default_timezone_set("Asia/Kolkata");
      return date( 'Ymd-hm' );
    } else {
      return $title;
    }
}
/* ---4 ---*/
/*
5. Disable Self Pingbacks
Source: https://www.wpstuffs.com/disable-self-pingbacks/
*/
add_filter( 'title_save_pre', 'filter_title_save_pre', 10, 1 );

function disable_self_trackback( &$links ) {
  foreach ( $links as $l => $link )
        if ( 0 === strpos( $link, get_option( 'home' ) ) )
            unset($links[$l]);
}

add_action( 'pre_ping', 'disable_self_trackback' );
/* ---5 ---*/

/*
6. Disable Gutenberg
Source: https://github.com/dimadin/disable-block-editor
*/
add_filter( 'use_block_editor_for_post', '__return_false', 666 );

/* ---6--- */

/*
7.  add emojis 
*/
add_filter( 'the_title', 'addemojis', 10, 2 );
function addemojis( $title, $id ) {
if( in_category( 'Books', $id ) ) {
$title = '📚 ' . $title;
}
elseif (in_category( 'Television', $id )) {
	$title = '📺 ' . $title;
}
elseif (in_category( 'Movies', $id )) {
	$title = '🎬 ' . $title;
}
elseif (in_category( 'Listens', $id )) {
	$title = '🎧 ' . $title;
}
return $title;
}

/* ---7---  */

/*
8. Add Post Kinds to Feeds
Source: https://danq.me/2020/03/01/post-kinds-rss/
*/

// Make titles in RSS feed be prefixed by the Kind of the post.
function add_kind_to_rss_post_title(){
	$kinds = wp_get_post_terms( get_the_ID(), 'kind' );
	if( ! isset( $kinds ) || empty( $kinds ) ) return get_the_title(); // sanity-check.
	$kind = $kinds[0]->name;
	$title = get_the_title();
	return trim( "[{$kind}] {$title}" );
}
add_filter( 'the_title_rss', 'add_kind_to_rss_post_title', 4 ); // priority 4 to ensure it happens BEFORE default escaping filters.

/* ---8--- */

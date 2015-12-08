<?php
/**
 * @package YMBESEO\Main
 */

/**
 * Plugin Name: Yoast SEO
 * Version: 0.1.0
 * Plugin URI: https://yoast.com/wordpress/plugins/seo/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=ymbeseoplugin
 * Description: The first true all-in-one SEO solution for WordPress, including on-page content analysis, XML sitemaps and much more.
 * Author: Team Yoast
 * Author URI: https://yoast.com/
 * Text Domain: ymbeseo
 * Domain Path: /languages/
 * License: GPL v3
 */

/**
 * Yoast SEO plugin
 * Copyright (C) 2015-2016, SO WP - support@so-wp.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! defined( 'YMBESEO_FILE' ) ) {
	define( 'YMBESEO_FILE', __FILE__ );
}

// Load the Yoast SEO plugin.
require_once( dirname( __FILE__ ) . '/ymbeseo-main.php' );

<?php
/**
 * @package YMBESEO\Main
 */

/**
 * Plugin Name: Yoast Minus Bloat Equals SEO
 * Version:     0.1.0
 * Plugin URI:  https://so-wp.com/plugins/ymbeseo
 * Description: Yoast Minus Bloat Equals SEO is a fork from Yoast SEO 2.3.5.
 * Author:      SO WP
 * Author URI:  https://so-wp.com/
 * Text Domain: ymbeseo
 * Domain Path: /languages/
 * License:     GPL v3
 */

/**
 * Yoast Minus Bloat Equals SEO plugin
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

// Load the Yoast Minus Bloat Equals SEO plugin.
require_once( dirname( __FILE__ ) . '/ymbeseo-main.php' );

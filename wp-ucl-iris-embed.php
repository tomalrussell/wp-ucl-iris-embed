<?php
/*
Plugin Name:        UCL IRIS embed shortcode
Plugin URI:         https://github.com/tomalrussell/wp-ucl-iris-embed
Description:        Include UCL Iris/RPS publication data with a shortcode: [ucl_iris upi="MBAUM76"]
Version:            1.0.0
Author:             Tom Russell
Author URI:         https://github.com/tomalrussell

License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/

/**
 * Register shortcode, for use in any post or page
 *
 * Usage: [ucl_iris upi="MBAUM76"]
 * - where upi is the UCL Person Identifier
 *
 * @param  array $atts - upi or other parameters, documented at:
 *         http://www.ucl.ac.uk/isd/how-to/research-it/how-to-embed-rps
 * @return string $out - HTML for publication list
 */
function ucl_iris_embed_shortcode( $atts ) {
	// parse attributes from shortcode
	$atts = shortcode_atts(
		array(
			'upi' => '',
		),
		$atts
	);

	// display error if no UPI
	if(empty($atts['upi'])){
		return "Publications (missing UCL Person Identifier - example usage: [ucl_iris upi=\"MBAUM76\"] )";
	}

	// key for cache
	$key = 'ucl_iris_pubs_'.$atts['upi'];

	// try loading from WP_Transient cache
	if ( false === ( $pubs = get_transient( $key ) ) ) {

		// request from web service
		$url = 'http://sql-ssrs01.ad.ucl.ac.uk/RPSDATA.SVC/pubs/'.$atts['upi'];
		$response = wp_remote_get($url);
		if(is_wp_error($response)){
			return "Publications (request error - tried $url )";
		}

		// save in cache
		$pubs = wp_remote_retrieve_body($response);
		set_transient( $key, $pubs, 60*60*12 ); // 12 hours in seconds
	}

	// return HTML as provided, wrapped in a div for convenience
	$out = '<div class="ucl_iris_embed_wrap">';
	$out .= $pubs;
	$out .= '</div>';

	return $out;
}
add_shortcode( 'ucl_iris', 'ucl_iris_embed_shortcode' );
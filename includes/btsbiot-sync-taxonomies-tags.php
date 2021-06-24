<?php 

if (!defined('MCFLOAT_SYNC_TAXONOMIES_DEBUG'))
  define('MCFLOAT_SYNC_TAXONOMIES_DEBUG', false);

if (MCFLOAT_SYNC_TAXONOMIES_DEBUG) do_action('log', 'Central taxonomies: Init', $wpdb);
add_action('muplugins_loaded', 'mcfloat_central_taxonomies');
add_action('plugins_loaded', 'mcfloat_central_taxonomies');
add_action('init', 'mcfloat_central_taxonomies');
add_action('wp_loaded', 'mcfloat_central_taxonomies');
add_action('switch_blog', 'mcfloat_central_taxonomies');
add_action('template_redirect', 'mcfloat_central_taxonomies');
function mcfloat_central_taxonomies () {
  global $wpdb;

  $prefix = $wpdb->base_prefix;
  $wpdb->terms = $prefix."terms";
  $wpdb->term_taxonomy = $prefix."term_taxonomy";
  if (MCFLOAT_SYNC_TAXONOMIES_DEBUG) do_action('log', 'Central taxonomies', '!prefix,terms,term_relationships,term_taxonomy', $wpdb);
}
<?php

/**
* Get all activated extensions
*/
function get_active_extensions() {
  return get_option( 'my_plugin_active_extensions', array() );
}
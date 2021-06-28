<?php

/**
* Get all the registered extensions through a filter
*/
function get_extensions() {
  return apply_filters( 'my_plugin_extensions', array() );
}
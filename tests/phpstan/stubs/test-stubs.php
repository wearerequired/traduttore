<?php

/**
 * @param callable $callback
 * @param array $args
 *
 * @return string
 */
function get_echo( $callback, array $args = [] ) {
	ob_start();
	call_user_func_array( $callback, $args );
	return ob_get_clean();
}

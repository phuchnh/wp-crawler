<?php

if ( ! function_exists( 'value' ) ) {
	/**
	 * Return the default value of the given value.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	function value( $value ) {
		return $value instanceof Closure ? $value() : $value;
	}
}

if ( ! function_exists( 'array_flatten' ) ) {
	/**
	 * Flatten a multi-dimensional array into a single level.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	function array_flatten( $array ) {
		$return = [];
		array_walk_recursive( $array, static function ( $x ) use ( &$return ) {
			$return[] = $x;
		} );

		return $return;
	}
}

if ( ! function_exists( 'array_get' ) ) {
	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	function array_get( $array, $key, $default = null ) {
		if ( $key === null ) {
			return $array;
		}
		if ( isset( $array[ $key ] ) ) {
			return $array[ $key ];
		}
		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
				return value( $default );
			}
			$array = $array[ $segment ];
		}

		return $array;
	}
}

if ( ! function_exists( 'array_pluck' ) ) {
	/**
	 * Pluck an array of values from an array.
	 *
	 * @param array $array
	 * @param string $value
	 * @param string $key
	 *
	 * @return array
	 */
	function array_pluck( $array, $value, $key = null ) {
		$results = array();
		foreach ( $array as $item ) {
			$itemValue = data_get( $item, $value );
			// If the key is "null", we will just append the value to the array and keep
			// looping. Otherwise we will key the array using the value of the key we
			// received from the developer. Then we'll return the final array form.
			if ( $key === null ) {
				$results[] = $itemValue;
			} else {
				$itemKey             = data_get( $item, $key );
				$results[ $itemKey ] = $itemValue;
			}
		}

		return $results;
	}
}

if ( ! function_exists( 'data_get' ) ) {
	/**
	 * Get an item from an array or object using "dot" notation.
	 *
	 * @param mixed $target
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	function data_get( $target, $key, $default = null ) {
		if ( $key === null ) {
			return $target;
		}
		foreach ( explode( '.', $key ) as $segment ) {
			if ( is_array( $target ) ) {
				if ( ! array_key_exists( $segment, $target ) ) {
					return value( $default );
				}
				$target = $target[ $segment ];
			} elseif ( $target instanceof ArrayAccess ) {
				if ( ! isset( $target[ $segment ] ) ) {
					return value( $default );
				}
				$target = $target[ $segment ];
			} elseif ( is_object( $target ) ) {
				if ( ! isset( $target->{$segment} ) ) {
					return value( $default );
				}
				$target = $target->{$segment};
			} else {
				return value( $default );
			}
		}

		return $target;
	}
}

<?php
/** 
 * The PODE casting trait is designed to give an object more options in setting type casts for its
 * properties. This trait allows properties to have one or more type casts among the PHP basic data
 * types. It also allows for casting callback functions, which are functions that can be run with
 * arguments to determine whether a variable can be set as property to the object. The return value of
 * a casting callback function is interpetted as either TRUE or FALSE by podeCasting methods.
 *
 * @author nolorin
 * @author www.github.com/nolorin
 * @package pode_tools
 * @version 1.0
 * @since 1.0
 */
trait podeCasting {
	/**
	 * All user defined properties can be given a custom type cast, which may be the string name of a
	 * data type, an array containing the string names of accepted data types, or a callback function
	 * that can be run with arguments to output a boolean accepting or rejecting the variable. 
	 * @var array $pode_casts Associative array where keys are variable names and values are the type
	 * casts or callback functions associated with the key variable.
	 */
	protected array $pode_casts = array();
	/**
	 * Allows for custom error reporting.
	 * @var bool $pode_cating_errors_on Whether or not error messages for 'podeCasting' will be displayed
	 */
	public bool $pode_casting_errors_on = TRUE;
	/**
	 * Method for setting the type casts for a given variable. This method cannot assign callable casting, and
	 * it is not possible to have string type casts and a callback cast.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @param mixed $cast Type cast for variable, may be string or array of strings
	 * @return bool Returns TRUE when type cast is successfully set, otherwise returns FALSE.
	 */
	public function pode_cast_set( string $var_name, $cast ): bool {
		// Initialization of output variable
		$output = TRUE;
		// Check that all casts are of type string
		$casts = is_array( $cast ) ? $cast : array( $cast );
		foreach( $casts as $c ) {
			if( !is_string( $c ) ) {
				if( $this->pode_states_errors_on ) {
					trigger_error( "[PODE:casting] Variable cast must be a string or array of strings for method 'pode_cast_set'", E_USER_WARNING );
				}
				$output = FALSE;
			}
		}
		// Assign type cast(s)
		if( $output ) {
			$this->pode_casts[$var_name] = $casts;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for setting the type cast for a given variable to a callback function. A variable may only have
	 * one callback cast, and it is not possible to have both string type casts and a callback cast.
	 * @since 1.0
	 * @param string $var_name 
	 * @param callable $callback
	 * @return bool Returns TRUE when type cast is successfully set, otherwise returns FALSE.
	 */
	public function pode_cast_set_callback( string $var_name, callable $callback ): bool {
		if( $this->pode_casts[$var_name] = $callback ) {
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		return $output;
	}
	/**
	 * Method for adding the type casts for a given variable. This method cannot assign callable casting.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @param mixed $cast Type cast for variable, may be string or array of strings
	 * @return bool Returns TRUE when type cast is successfully set, otherwise returns FALSE.
	 */
	public function pode_cast_add( string $var_name, $cast ): bool {
		// Initialization of output variable
		$output = TRUE;
		// Check that all casts are of type string
		$casts = is_array( $cast ) ? $cast : array( $cast );
		foreach( $casts as $c ) {
			if( !is_string( $c ) ) {
				if( $this->pode_states_errors_on ) {
					trigger_error( "[PODE:casting] Variable cast must be a string or array of strings for method 'pode_cast_set'", E_USER_WARNING );
				}
				$output = FALSE;
			}
		}
		// Assign type cast(s)
		if( $output ) {
			$this->pode_casts[$var_name] = array_merge( $this->pode_casts[$var_name], $casts );
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for removing the type casts for a given variable. This method does not handle callable casting.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @param mixed $cast Type cast for variable, may be string or array of strings
	 * @return bool Returns TRUE when type cast is successfully set, otherwise returns FALSE.
	 */
	public function pode_cast_remove( string $var_name, $cast ): bool {
		// Initialization of output variable
		$output = TRUE;
		// Check that all casts are of type string
		$casts = is_array( $cast ) ? $cast : array( $cast );
		foreach( $casts as $c ) {
			if( !is_string( $c ) ) {
				if( $this->pode_states_errors_on ) {
					trigger_error( "[PODE:casting] Variable cast must be a string or array of strings for method 'pode_cast_set'", E_USER_WARNING );
				}
				$output = FALSE;
			}
		}
		// Assign type cast(s)
		if( $output ) {
			$this->pode_casts[$var_name] = array_diff( $this->pode_casts[$var_name], $casts );
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for checking whether a variable has the correct cast. If the variable cast is a callback
	 * function, the function is run with the variable value as an argument; to use other arguments for the
	 * callback, the 'pode_cast_check_callback' method must be used. If the callback returns TRUE, then
	 * the variable is considered to have the correct type; if it returns FALSE then the variable is considered
	 * to have the wrong type.
	 * @since 1.0
	 * @param string $var_name Name of varialbe
	 * @param mixed $var_value Value of variable
	 * @return mixed Returns TRUE if the variable value is of a type that is included in the pre-defined
	 * type casts for that variable, or if the casting callback function returns a value of TRUE, otherwise
	 * the method returns FALSE.
	 */
	public function pode_cast_check( string $var_name, $var_value ) {
		// Check to see if casts are registered for $variable; returns an notice if none are defined
		if( !empty( $this->pode_casts[$var_name] ) ) {
			if( !is_callable( $this->pode_casts[$var_name] ) ) {
				// Loops through allowed type casts to see if one matches input variable type
				$output = FALSE;
				foreach( $this->pode_casts[$var_name] as $cast ) {
					if( gettype( $var_value ) == $cast ) {
						$output = TRUE;
					}
				}
			} else {
				// Runs casting callback function
				$output = $this->pode_cast_check_callback( $var_name, [ $var_value ] );
			}
		} else {
			if( $this->pode_states_errors_on ) {
				trigger_error( "[PODE:casting] Variable of name '$var_name' does not have a registered cast.", E_USER_NOTICE );
			}
			$output = NULL;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for running the casting callback function for a variable to see if variable is of correct type.
	 * If the callback returns TRUE, then the variable is considered to have the correct type; if it returns 
	 * FALSE then the variable is considered to have the wrong type.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @param array $args Arguments for the casting callback function
	 * @return mixed Returns TRUE if the callback function returns TRUE, returns FALSE if the callback
	 * function returns FALSE, and returns NULL if the casting for the variable is not a callable.
	 */
	public function pode_cast_check_callback( string $var_name, array $args = NULL ) {
		// Check that the casting for the variable is callable
		if( is_callable( $this->pode_casts[$var_name] ) ) {
			// Convert the return value of casting callback into a boolean
			$callback_output = $this->pode_casts[$var_name]( ...$args );
			$output = $callback_output ? TRUE : FALSE;
		} else {
			if( $this->pode_states_errors_on ) {
				trigger_error( "[PODE:casting] Variable of name '$var_name' does not have a callback cast", E_USER_WARNING );
			}
			$output = NULL;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for unsetting the casting for a variable.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @return Always returns TRUE.
	 */
	public function pode_cast_unset( string $var_name ) {
		unset( $this->pode_casts[$var_name] );
		return TRUE;
	}
}

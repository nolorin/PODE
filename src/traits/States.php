<?php
namespace PODE\Traits;

/** 
 *
 * @author nolorin
 * @author www.github.com/nolorin
 * @package pode_tools
 * @version 1.1
 * @since 1.0
 */
trait podeStates {
	/**
	 * Variable states are recorded in this property associative array, in which the keys are
	 * the variable names and the values are arrays containing the string states to which the given
	 * variable can be set.
	 * @var array $pode_states Associative array containing variable names and the states that
	 * each variable is allowed to have.
	 */
	protected array $pode_states = array();
	/**
	 * The current variable state for each variable is contained in this property associative array, in which
	 * the keys are the variable names and the values are the corresponding current states for those variables.
	 * @var array $pode_states_current Associative array containing variable names and their current state.
	 */
	protected array $pode_states_current = array();
	/**
	 * Allows for custom error reporting.
	 * @var bool $pode_states_errors_on Whether or not error messages for 'podeStates' will be displayed
	 */
	public bool $pode_states_errors_on = TRUE;
	/**
	 * Method for setting all of the possible states for a given variable.
	 * @param string $var_name Name of the variable
	 * @param mixed $states May be a single string state or an array containing multiple string states
	 * @return bool Returns TRUE if states are successfully set, and returns FALSE if any of the states
	 * in the method argument '$state' are not of type string.
	 */
	public function pode_states_set( string $var_name, $state ): bool {
		// Initialization of output variable, assuming TRUE as default
		$output = TRUE;
		// Conver $state into $states array
		$states = is_array( $state ) ? $states : array( $state );
		// Loop through states to check that they are all of type string
		foreach( $states as $state ) {
			if( !is_string( $state ) ) {
				if( $this->pode_states_errors_on ) {
					trigger_error( "[PODE:states] Variable state declarations must be of type string", E_USER_WARNING );
				}
				$output = NULL;
				break;				
			}
		}
		// Only sets sets states if all items in $states are string
		if( $output ) {
			$this->pode_states[$var_name] = $states;			
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for adding one or more possible states for a given variable.
	 * @param string $var_name Name of the variable
	 * @param mixed $states May be a single string state or an array containing multiple string states
	 * @return bool Returns TRUE if state(s) are successfully added, and returns FALSE if any of the states
	 * in the method argument '$states' are not of type string. No states will be added except if all the
	 * items in $states are strings.
	 */
	public function pode_states_add( string $var_name, $state ): bool {
		// Initialization of output variable, assuming TRUE as default
		$output = TRUE;
		// Converting $state argument into array
		$states = is_string( $state ) ? array( $state ) : ( is_array( $state ) ? $state : FALSE );
		if( $states !== FALSE ) {
			// Loop through states to check that they are all of type string
			foreach( $states as $state ) {
				if( !is_string( $state ) ) {
					if( $this->pode_states_errors_on ) {
						trigger_error( "[PODE:states] Variable state declarations must be of type string", E_USER_WARNING );
					}
					$output = NULL;
					break;
				}
			}
			// Only adds states if all items in $states are string
			if( $output ) {
				$this->pode_states[$var_name] = !empty( $this->pode_states[$var_name] ) ? $this->pode_states[$var_name] : array();
				foreach( $states as $state ) {
					if( !in_array( $state, $this->pode_states[$var_name] ) ) {
						$this->pode_states[$var_name][] = $state;
					} else if( $this->pode_states_errors_on ) {
						trigger_error( "[PODE:states] State '$state' already registered for '$var_name'", E_USER_NOTICE );
					}
				}
			}
		} else {
			if( $this->pode_states_errors_on ) {
				trigger_error( "[PODE:states] Argument $state for method 'pode_states_add' must be of type string or array", E_USER_WARNING );
			}
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for removing one or more possible states for a given variable.
	 * @param string $var_name Name of the variable
	 * @param mixed $state May be a single string state or an array containing multiple string states
	 * @return mixed Returns TRUE if states are successfully unset, returns FALSE if the method argument
	 * $state is not a string or an array, and returns NULL if the requested variable is not defined.
	 */
	public function pode_states_remove( string $var_name, $state ) {
		// Converting $state argument into array
		$states = is_string( $state ) ? array( $state ) : ( is_array( $state ) ? $state : FALSE );
		if( $states !== FALSE ) {
			// Check to see if variable exists
			if( !empty( $this->pode_states[$var_name] ) ) {
				// Loop through allowed states for variable, unsetting those that are marked to be unset
				for( $i=0; $i<count( $this->pode_states[$var_name] ); $i++ ) {
					if( in_array( $this->pode_states[$var_name][$i], $states ) ) {
						unset( $this->pode_states[$var_name][$i] );
					}
				}
				$this->pode_states[$var_name] = array_values( $this->pode_states[$var_name] );
				if( !in_array( $this->pode_states_get_current( $var_name ), $this->pode_states[$var_name] ) ) {
					$this->pode_states_clear_current( $var_name );
				}
				$output = TRUE;
			} else {
				if( $this->pode_states_errors_on ) {
					trigger_error( "[PODE:states] Variable of name '$var_name' does not exist", E_USER_WARNING );
				}
				$output = NULL;
			}
		} else {
			if( $this->pode_states_errors_on ) {
				trigger_error( "[PODE:states] Argument $state for method 'pode_states_remove' must be of type string or array", E_USER_WARNING );
			}
			$output = FALSE;
		}
		// Method completion
		return $output;
	}

	/**
	 * Method for for setting the current state of a variable, as long as that current state is an allowed
	 * state found in the property variable 'pode_states[var_name]'.
	 * @param string $var_name Name of variable
	 * @param string $state Variable state that is to be set as current
	 * @return bool Returns TRUE if the variable state is successfully set, otherwise returns FALSE.
	 */
	public function pode_states_set_current( string $var_name, string $state ): bool {
		// Check that current state is allowed
		if( in_array( $state, $this->pode_states[$var_name] ) ) {
			$this->pode_states_current[$var_name] = $state;
			$output = TRUE;
		} else {
			if( $this->pode_states_errors_on ) {
				trigger_error( "[PODE:states] State cannot be set for variable '$var_name' because state '$state' is not recognized." );
			}
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method that retrieves the current state of a variable.
	 * @param string $var_name Name of variable
	 * @return string Returns the current state of the requested variable.
	 */
	public function pode_states_get_current( string $var_name ): string {
		return $this->pode_states_current[$var_name];
	}
	/**
	 * Method that clears the current state of a variable
	 * @param string $var_name Name of variable
	 * @return bool Always returns TRUE.
	 */
	public function pode_states_clear_current( string $var_name ): bool {
		$this->pode_states_current[$var_name] = NULL;
		return TRUE;
	}
}

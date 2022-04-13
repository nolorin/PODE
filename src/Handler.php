<?php
namespace PODE;

use PODE\Traits\Permissions as Permissions;
use PODE\Traits\Casting as Casting;
use PODE\Traits\States as States;

/** 
 * 
 * @author nolorin
 * @author www.github.com/nolorin
 * @package pode_tools
 * @version 1.1
 * @since 1.0
 */

class Handler {
	use Permissions, Casting, States;
	/**
	 * All of the user defined variables for the 'Handler' class are stored in a single
	 * property variable called $vars, and variables in this array can only be altered or
	 * accessed via methods.
	 * @var array $vars
	 * @since 1.0
	 */
	protected array $vars = array();
	/**
	 * Allows for custom error reporting.
	 * @var bool $erros_on Whether or not error messages for 'Handler' will be displayed
	 */
	protected bool $errors_on = TRUE;
	/**
	 * Method for setting the error reporting for methods in this trait.
	 * @param bool $on Whether error reporting is on or off
	 */
	public function errors_on( bool $on ) {
		$this->pode_casting_errors_on = $on;
		$this->pode_states_errors_on = $on;
		$this->pode_perms_errors_on = $on;
		$this->errors_on = $on;
	}
	/**
	 * Magic method '__set' automatically returns the public method 'set', which implements the
	 * data controls defined in the traits that 'Handler' uses.
	 * @param string $name Name of variable to be set
	 * @param mixed $value Value of variable to be set
	 * @return bool Returns the output of method 'Handler:set'.
	 */
	public function __set( string $name, $value ) {
		return $this->set( $name, $value );
	}
	/**
	 * Magic method '__get' automatically returns the public method 'get', which implements the
	 * data controls defined in the traits that 'Handler' uses.
	 * @param string $name Name of requested variable
	 * @return bool Returns the output of method 'Handler:get'.
	 */
	public function __get( string $name ) {
		return $this->get( $name );
	}
	/**
	 * Magic method '__unset' automatically returns the public method 'unset', which implements the
	 * data controls defined in the traits that 'Handler' uses.
	 * @param string $name Name of variable to be unset
	 * @return bool Returns the output of method 'Handler:unset'.
	 */
	public function __unset( string $name ) {
		return $this->unset( $name );
	}
	/**
	 * Magic method '__debugInfo' automatically hides the values of encryption variables.
	 * @return array Edited debugging info.
	 */
	public function __debugInfo(): array {
		$output = clone $this;
		foreach( [ 'pode_users', 'pode_var_access', 'pode_user_perms' ] as $arr ) {
			foreach( $output->$arr as $name => $key ) {
				$output->$arr[$name] = str_repeat( '?', strlen( $key ) );
			}
		}
		$output->pode_admin_pass = str_repeat( '?', strlen( $output->pode_admin_pass ) );
		$output = (array) $output;
		return $output;
	}
	/**
	 * Method for setting variables to object. This method first checks that the variable is accessible, then it checks
	 * that the user has permission to set the variable, and finally it checks to see if the variable value is of the
	 * the correct type cast.
	 * @param string $name Name of the variable that is to be set
	 * @param mixed $value Value of the variable that is to be set
	 * @param string $uname Name of the user trying to set the variable
	 * @param string $upass Password for the user trying to set the variable
	 * @param array $pargs The arguments that are used in callbacks to check permissions
	 * @param array $cargs The arguments that are used in callbacks to check casting
	 * @return bool Returns TRUE when the variable is successfully set, otherwise returns FALSE.
	 */
	public function set( string $var_name, $value, string $uname = NULL, string $upass = NULL, array $pargs = [], array $cargs = [] ): bool {
		// Check to see if variable is accessible
		if( !empty( $this->pode_var_access[$var_name] ) ) {
			// Check that the user trying to access the variable either has permission or is the admin
			if( @$this->pode_admin_check( $uname, $upass ) || $this->pode_var_check_access( $var_name, $uname, $upass, $pargs ) ) {
				// Check to see if the variable is type cast
				if( !empty( $this->pode_casts[$var_name] ) ) {
					// If casting for variable is callable, run function with casting arguments
					if( is_callable( $this->pode_casts[$var_name] ) && $this->pode_casts[$var_name]( ... $cargs ) ) {
						$this->vars[$var_name] = $value;
						$output = TRUE;
					// Check that variable value is of correct type cast
					} else if( in_array( gettype( $value ), $this->pode_casts[$var_name] ) ) {
						$this->vars[$var_name] = $value;
						$output = TRUE;
					// Return error
					} else {
						if( $this->errors_on ) {
							$message = "[PODE:handler] Variable '$var_name' cannot be set because it is the wrong type or ";
							$message .= "because it the callable casting failed";
							trigger_error( $message, E_USER_NOTICE );							
						}
						$output = FALSE;
					}
				} else {
					$this->vars[$var_name] = $value;
					$output = TRUE;
				}
			} else {
				if( $this->errors_on) {
					trigger_error( "[PODE:handler] Access denied for variable '$var_name'", E_USER_NOTICE );					
				}
				$output = FALSE;
			}
		}
		// Method completion
		if( $output ) {
			$this->pode_states[$var_name] = array();
			$this->pode_states_current[$var_name] = NULL;
		}
		return $output;
	}
	/**
	 * Method for retrieving variables stored in object. This method first checks that the variable is accessible, then
	 * it checks that the user has permission to access the variable.
	 * @param string $var_name Name of the variable that is to be set
	 * @param string $uname Name of the user trying to set the variable
	 * @param string $upass Password for the user trying to set the variable
	 * @param array $pargs The arguments that are used in callbacks to check permissions
	 * @return mixed Returns requested variable if user name and password are corrected or not required,
	 * otherwise returns FALSE.
	 */
	public function get( string $var_name, string $uname = NULL, string $upass = NULL, array $pargs = [] ) {
		// Check to see if variable is accessible
		if( !empty( $this->pode_var_access[$var_name] ) ) {
			// Check to see if user has access; if user is admin, then access is automatically granted
			if( @$this->pode_admin_check( $uname, $upass ) || $this->pode_var_check_access( $var_name, $uname, $upass, $pargs ) ) {
				$output = $this->vars[$var_name];
			} else {
				if( $this->errors_on ) {
					trigger_error( "[PODE:handler] Access denied for variable '$var_name'", E_USER_NOTICE );
				}
				$output = FALSE;
			}
		} else {
			if( $this->errors_on ) {
				trigger_error( "[PODE:handler] Requested variable access is not defined", E_USER_WARNING );
			}
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for unsetting variables stored in object. This method first checks that the variable is accessible, then
	 * it checks that the user has permission to access the variable.
	 * @param string $var_name Name of the variable that is to be set
	 * @param string $uname Name of the user trying to set the variable
	 * @param string $upass Password for the user trying to set the variable
	 * @param array $pargs The arguments that are used in callbacks to check permissions
	 * @return bool Returns true if variable is successfully unset, otherwise returns FALSE.
	 */
	public function unset( string $var_name, string $uname = NULL, string $upass = NULL, array $pargs = [] ): bool {
		// Check to see if variable is accessible
		if( !empty( $this->pode_var_access[$var_name] ) ) {
			// Check to see if user has access; if user is admin, then access is automatically granted
			if( @$this->pode_admin_check( $uname, $upass ) || $this->pode_var_check_access( $var_name, $uname, $upass, $pargs ) ) {
				unset( $this->vars[$var_name] );
				$output = TRUE;
			} else {
				if( $this->errors_on ) {
					trigger_error( "[PODE:handler] Access denied for variable '$var_name'", E_USER_NOTICE );
				}
				$output = FALSE;
			}
		} else {
			if( $this->errors_on ) {
				trigger_error( "[PODE:handler] Requested variable access is not defined", E_USER_WARNING );
			}
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
}

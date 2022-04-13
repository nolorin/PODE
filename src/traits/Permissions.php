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
trait podePermissions {
	/**
	 * Objects with this trait have a single administrator, which is authenticated with a password that is
	 * hashed using the PHP core function 'password_hash( $password, PASSWORD_DEFAULT )'.
	 * @since 1.0
	 * @var string $pode_admin_name Name of the PODE admin for object
	 */
	protected ?string $pode_admin_name = NULL;
	/**
	 * Objects with this trait have a single administrator, which is authenticated with a password that is
	 * hashed using the PHP core function 'password_hash( $password, PASSWORD_DEFAULT )'.
	 * @since 1.0
	 * @var string $pode_admin_pass Password for the PODE admin for object
	 */
	protected ?string $pode_admin_pass = NULL;
	/**
	 * The PODE administrator for the object cannot be reset after it is first set if this property is set
	 * equal to FALSE. The only way to change this property is with the administrator's name and password,
	 * after which this property can be set to TRUE and the administrator can be reset.
	 * @since 1.0
	 * @var bool $pode_admin_regenerate
	 */
	protected bool $pode_admin_regenerate = FALSE;

	/**
	 * Users registered to this object are stored in a property array that contains the user names
	 * and the hashes for their passwords. Passwords are hashed using the PHP core function
	 * 'password_hash( $password, PASSWORD_DEFAULT )'.
	 * @since 1.0
	 * @var array $pode_users Associative array containing user names and password hashes
	 */
	protected array $pode_users = array();
	/**
	 * User permissions are stored in a property array.
	 * @since 1.0
	 * @var array $pode_user_perms Associative array containing user names and permissions
	 */
	protected array $pode_user_perms = array();

	/**
	 * Variable access in 'podePermissions' refers to the the user permission requirements that must
	 * be met before the variable can be altered or retrieved.
	 * @since 1.0
	 * @var array $pode_var_access Associative array where the key is the name of a variable and
	 * the value is a string containing the access requirements before the variable can be altered
	 * or retreived.
	 */
	protected array $pode_var_access = array();

	/**
	 * Allows for custom error reporting.
	 * @var bool $pode_perms_errors_on Whether or not error messages for 'podePermissions' will be displayed
	 */
	public bool $pode_perms_errors_on = TRUE;

	/**
	 * Method for checking whether the administrator name and password for this object are correct. The password is checked
	 * using the PHP core function 'password_verify'.
	 * @since 1.0
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @param string $method Name of method where super admin check is occurring
	 * @return bool Returns TRUE when admin name and password are valid, otherwise returns FALSE.
	 */
	protected function pode_admin_check( ?string $admin_name, ?string $admin_pass = NULL, string $method = NULL ): bool {
		// Admin privileges granted automatically if admin is not set
		if(	$this->pode_admin_name === NULL || ( $this->pode_admin_name == $admin_name && password_verify( $admin_pass, $this->pode_admin_pass ) ) ) {
			$output = TRUE;
		} else {
			if( $this->pode_states_errors_on ) {
				$method_text = !empty( $method ) ? ", for method '$method'" : "";
				trigger_error( "[PODE:permissions] Admin name and password are invalid$method_text", E_USER_WARNING );
			}
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for changing the object property 'pode_admin_regenerate'. If this property is set to FALSE and the admin
	 * has already been set, a new admin cannot be set using this method unless the correct admin name and password are
	 * provided.
	 * @since 1.0
	 * @param bool $regenerate Whether or not admin can be regenerated
	 * @param string $admin_name Name of current admin
	 * @param string $admin_pass Password for current admin
	 * @return bool Returns TRUE if admin regeneration is set, and returns FALSE if current admin name and password
	 * are not valid.
	 */
	public function pode_admin_regenerate( bool $regenerate, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			$this->pode_admin_regenerate = $regenerate;
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for setting the PODE administrator for this object. This method will fail if the object property 
	 * 'pode_admin_regenerate' is set to FALSE.
	 * @since 1.0
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE when admin set is successful, otherwise returns FALSE.
	 */
	public function pode_admin_set( string $admin_name, string $admin_pass ): bool {
		// Check to see if admin regeneration is allowed, and then if admin is already defined
		if( !$this->pode_admin_regenerate && $this->pode_admin_name !== NULL ) {
			if( $this->pode_states_errors_on ) {
				trigger_error( "[PODE:permissions] Admin regeneration is not activated for object  and admin is not null", E_USER_WARNING );
			}
			$output = FALSE;
		} else {
			// Set admin
			$this->pode_admin_name = $admin_name;
			$this->pode_admin_pass = password_hash( $admin_pass, PASSWORD_DEFAULT );
			$output = TRUE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for reseting the PODE administrator for this object by setting the property values of 'pode_admin_name'
	 * and 'pode_admin_pass' to NULL. This method will fail if the PODE administrator is already set and the object property
	 * 'pode_admin_regenerate' is set to FALSE. The method will return TRUE even if 'pode_admin_regenerate' is set to false
	 * if the 'pode_admin_name' is already equal to NULL.
	 * @since 1.0
	 * @return bool Returns TRUE when admin reset is successful, otherwise returns FALSE.
	 */
	public function pode_admin_reset() {
		// Check to see if admin regeneration is allowed, and then if admin is already defined
		if( !$this->pode_admin_regenerate && $this->pode_admin_name !== NULL ) {
			if( $this->pode_states_errors_on ) {
				trigger_error( "[PODE:permissions] Admin regeneration is not activated for object and admin is not null", E_USER_WARNING );
			}
			$output = FALSE;
		} else {
			$this->pode_admin_name = $this->pode_admin_pass = NULL;
			$output = TRUE;
		}
		// Method completion
		return $output;
	}

	/**
	 * Method checks whether a user name and password are valid using the PHP core function 'password_verify'.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $upass Password of user
	 * @param string $method Name of method where admin check is occurring
	 * @return bool Returns TRUE if user name and password are correct, otherwise returns FALSE.
	 */
	protected function pode_user_check( ?string $uname, ?string $upass, string $method = NULL ): bool {
		if( !empty( $this->pode_users[$uname] ) && password_verify( $upass, $this->pode_users[$uname] ) ) {
			$output = TRUE;
		} else {
			if( $this->pode_states_errors_on ) {
				$method_text = !empty( $method ) ? ", for method '$method'" : "";
				trigger_error( "[PODE:permissions] User name and password are invalid$method_text", E_USER_WARNING );
			}
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method adds a user to user registery, 'pode_users'. User password is converted into a hash using the
	 * PHP core function 'password_hashe( $password, PASSWORD_DEFAULT )'. If the PODE administrator for this
	 * object is already set, then the correct admin name and password must be provided.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $upass Password of user
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE if user is successfully added, otherwise returns FALSE.
	 */
	public function pode_user_add( string $uname, string $upass = NULL, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// CHeck admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			$this->pode_users[$uname] = $upass !== NULL ? password_hash( $upass, PASSWORD_DEFAULT ) : NULL;
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method retrieves object property, 'pode_users', i.e. all the users that areregistered to this object. If 
	 * the PODE administrator for this object is already set, then the correct admin name and password must be provided.
	 * @since 1.0
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return mixed Returns FALSE if admin name and password are not valid, otherwise returns all users.
	 */
	public function pode_user_get_all( string $admin_name = NULL, string $admin_pass = NULL ) {
		// Check admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			$output = array_keys( $this->pode_users );
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method checks to see if a user name is registered in this object. If the PODE administrator for this object 
	 * is already set, then the correct admin name and password must be provided.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return ?bool Returns TRUE if object contains user in property 'pode_users', returns FALSE if user is
	 * not in 'pode_users', and returns NULL if admin name and password are not valid.
	 */
	public function pode_user_has( string $uname, string $admin_name = NULL, string $admin_pass = NULL ): ?bool {
		// Check admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			if( !empty( $this->pode_users[$uname] ) ) {
				$output = TRUE;
			} else {
				$output = FALSE;
			}
		} else {
			$output = NULL;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method removes users from the user registry for this object. If the PODE administrator for this object is 
	 * already set, then the correct admin name and password must be provided.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE if user is successfully removed, otherwise returns FALSE.
	 */
	public function pode_user_remove( string $uname, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			foreach( $this->pode_users as $name => $pass  ) {
				if( $name == $uname ) {
					unset( $this->pode_users[$name] );
				}
			}
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for setting the permissions for a given user already registered to this object. If the PODE administrator
	 * for this object is already set, then the correct admin name and password must be provided.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE if user permissions are successfully set, otherwise returns FALSE.
	 */
	public function pode_user_set_perms( string $perms, string $uname, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			$this->pode_user_perms[$uname] = $perms;
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for setting the permissions callbacak for a given user already registered to this object. If the PODE 
	 * administrator for this object is already set, then the correct admin name and password must be provided.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE if user permissions callback is successfully set, otherwise returns FALSE.
	 */
	public function pode_user_set_perm_callback( callable $perms, string $uname, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			$this->pode_user_perms[$uname] = $perms;
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for adding user permissions for a given user already registered to this object. If the PODE administrator 
	 * for this object is already set, then the correct admin name and password must be provided.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE if user permissions are successfully added, otherwise returns FALSE.
	 */
	public function pode_user_add_perms( string $perms, string $uname, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			$this->pode_user_perms[$uname] .= $perms;
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for retrieving the permissions for a user already registered to this object. The correct name and password
	 * for the user must be provided. If the method argument '$admin_name' is not NULL, then '$upass' is assumed to be the
	 * password for the admin.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $upass Password of user
	 * @param string $admin_name Name of admin
	 * @return mixed Returns user permissions if access is granted, otherwise returns FALSE.
	 */
	public function pode_user_get_perms( string $uname, string $upass, string $admin_name = NULL ) {
		// Check that user name and password are valid
		if( $this->pode_user_check( $uname, $upass, __FUNCTION__ ) || $this->pode_admin_check( $admin_name, $upass, __FUNCTION__ ) ) {
			return $this->pode_user_perms[$uname];
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for running the permissions callback function for a given user already registered to this object. The 
	 * correct name and password for the user must be provided. If the method argument '$admin_name' is not NULL, 
	 * then '$upass' is assumed to be the password for the admin.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $upass Password of user
	 * @return mixed Returns the output of user permissions callback function if user name and password are
	 * correct, or if user is admin, otherwise returns FALSE.
	 */
	public function pode_user_run_perm_callback( string $uname, string $upass = NULL, array $args = [], string $admin_name = NULL ) {
		// Check that user name and password are valid
		if( !empty( $admin_name ) ) {
			$check = $this->pode_admin_check( $admin_name, $upass, __FUNCTION__ );
		} else {
			$check = $this->pode_user_check( $uname, $upass, __FUNCTION__ );
		}
		// Run callback
		if( $check ) {
			if( is_callable( $this->pode_user_perms[$uname] ) ) {
				$callback_output = $this->pode_user_perms[$uname]( ...$args );
				$output = $callback_output ? TRUE : FALSE; // Convert callback output into boolean
			} else {
				if( $this->pode_states_errors_on ) {
					trigger_error( "[PODE:permissions] User permissions are not callable", __FUNCTION__ );
				}
				$output = FALSE;
			}
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for removing user permissions for a given user already registered to this object. If the PODE administrator 
	 * for this object is already set, then the correct admin name and password must be provided.
	 * @since 1.0
	 * @param string $perms The permissions to be removed; this string should be formated for regular expressions
	 * @param string $uname Name of user
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE if user permissions are successfully removed, otherwise returns FALSE.
	 */
	public function pode_user_remove_perms( string $perms, string $uname, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) && is_string( $this->pode_user_perms[$uname] ) ) {
			$this->pode_user_perms[$uname] = preg_replace( "/$perms/", '', $this->pode_user_perms[$uname] );
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for clearing user permissions for a given user already registered to this object. If the PODE administrator 
	 * for this object is already set, then the correct admin name and password must be provided.
	 * @since 1.0
	 * @param string $uname Name of user
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE if user permissions are successfully cleared, otherwise returns FALSE.
	 */
	public function pode_user_clear_perms( string $uname, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			$this->pode_user_perms[$uname] = NULL;
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}

	/**
	 * Method for setting the access requirements for a variable. If a PODE administrator has been set, then the correct
	 * admin name and password must also be provided.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @param string $access Access requirements for variable; this string should be formated for regular expressions
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE when variable access is successfully set, otherwise returns FALSE.
	 */
	public function pode_var_set_access( string $var_name, string $access, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check for admin access
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			$this->pode_var_access[$var_name] = $access;
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for adding the access requirements for a variable. If a PODE administrator has been set, then the correct
	 * admin name and password must also be provided.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @param string $access Access requirements for variable; this string should be formated for regular expressions
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE when variable access is successfully added, otherwise returns FALSE.
	 */
	public function pode_var_add_access( string $var_name, string $access, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check for admin access
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			$this->pode_var_access[$var_name] .= $access;
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for checking if a user has access to a give variable. The correct user name and password must be given
	 * in order to access the the user permissions; if user permissions are defined by a callback function, then function
	 * arguments may be provided. If the user is the PODE administrator, access is automatically given.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @param string $uname Name of user
	 * @param string $upass Password of user
	 * @param array $pargs The arguments that are used in callbacks to check permissions
	 * @return bool Returns TRUE if the user has access to the variable, otherwise returns FALSE.
	 */
	public function pode_var_check_access( string $var_name, ?string $uname = NULL, ?string $upass = NULL, array $pargs = NULL ): bool {
		// Check variable access restrictions
		$vaccess = !empty( $this->pode_var_access[$var_name] ) ? $this->pode_var_access[$var_name] : NULL;
		if( !empty( $vaccess ) ) {
			// Check if user is admin 
			if( @$this->pode_admin_check( $uname, $upass ) ) {
				$output = TRUE; // Admin always has access
			// Check if user name and password are valid
			} else if( @$this->pode_user_check( $uname, $upass ) ) { // Error suppressed because user and password not required
				// Get user permissions
				$uperms = is_callable( $this->pode_user_perms[$uname] ) ? $this->pode_user_perms[$uname]( ...$pargs ) : $this->pode_user_perms[$uname];
				// Use regular expression to check that user meets access restrictions
				if( empty( $vaccess ) || preg_match( '/[' . $uperms . ']+/', $vaccess ) ) {
					$output = TRUE;
				} else {
					$output = FALSE;
				}
			} else {
				$output = FALSE;
			}			
		} else {
			$output = TRUE; // No access restrictions means access is always granted
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for removing the access requirements for a variable. If a PODE administrator has been set, then the correct
	 * admin name and password must also be provided.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @param string $access Access requirements for variable to be removed; this string should be formated for regular expressions
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE when variable access is successfully altered, otherwise returns FALSE.
	 */
	public function pode_var_remove_access( string $var_name, string $access, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check admin name and password
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) && is_string( $this->pode_var_access[$var_name] ) ) {
			$this->pode_var_access[$var_name] = preg_replace( "/$access/", '', $this->pode_var_access[$var_name] );
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for clearing the access requirements for a variable. If a PODE administrator has been set, then the correct
	 * admin name and password must also be provided.
	 * @since 1.0
	 * @param string $var_name Name of variable
	 * @param string $admin_name Name of admin
	 * @param string $admin_pass Password for admin
	 * @return bool Returns TRUE if access is successfully cleared, otherwise returns FALSE.
	 */
	public function pode_var_clear_access( string $var_name, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check that admin name and pass are correct
		if( $this->pode_admin_check( $admin_name, $admin_pass, __FUNCTION__ ) ) {
			unset( $this->pode_var_access[$var_name] );
			$output = TRUE;
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
}

<?php
/** 
 * The access verify class for the PODE toolkit is designed to handle access across multiple scopes
 * and with multiple users. This class is useful for managing permissions and can be an efficient
 * API between backend coding and database servers. It can also be useful as an authentication keychain
 * when multiple access levels or authenticating users are being processed in the same session or using
 * seralized code. The class is not extendable so as to prevent malicious code compromising the
 * verification system, which relies on boolean function returns for its methods.
 *
 * Each scope is referenced by an "access class", which has a set access code. Each access class may 
 * have a whitelist of associated users, so that any users who are not on the whitelist are automatically
 * denied access. Additionally, access classes may have one or more administrators who are identified by a
 * password; administrators may change the access code for their access class, add users to that class's
 * whitelist, and remove users from the whiteless.
 * 
 * This class uses the PHP core functions 'password_hash' and 'password_verify' for hashing
 * access codes and checking access codes and admin passwords against previously recorded
 * hashes. The hashing algorithm that is used for both functions is PASSWORD_DEFAULT.
 *
 * @author nolorin
 * @author www.github.com/nolorin
 * @package pode_tools
 * @version 1.0
 * @since 1.0
 */
final class podeAccessVerify {
	/** 
	 * The codes used to unlock access to specific classes are all contained in the property array
	 * 'access_codes', which is an associative array where the keys are the access classes and the
	 * values are the password hashed accesscodes.
	 * @var array $access_codes The property array where access classes and code hashes are stored.
	 * @since 1.0
	 */
	protected array $access_codes = array();
	/** 
	 * Each access class may have one or more administrators who can associate users with a particular
	 * access class. Admin information is stored in the 'access_admins' property array, which is an
	 * associative array where the key is the name of the access class and the value is an array
	 * containing all the admins for that class.
	 * @var array $access_admins The property array indicating the admins for access classes.
	 * @since 1.0
	 */
	protected array $access_admins = array();
	/** 
	 * Each access class has associated users stored in the 'associated_users' propety array, which is
	 * an associative array where the key is the access class and the value is an array of the users associated
	 * with that class. If an action class has one or more associated users and a non-associated user tries
	 * to get access, access will be denied even if the user provides a correct access code; if the action class
	 * has zero associated users, then any user (or a NULL user) can gain access with the correct code.
	 * @var array $associated_users
	 * @since 1.0
	 */
	protected array $associated_users = array();
	/** 
	 * Method for setting the access code for a given class. If necessary, an admin name and password can
	 * be provided.
	 * @param string $access_class Name of the access class
	 * @param string $access_code The access code before it is processed by 'password_hash'
	 * @param string $admin_name The name of the admin
	 * @param string $admin_pass The password that verifies the admin
	 * @return mixed Returns TRUE if password is successfully set, FALSE if it is unsuccessful, and NULL if 
	 * there is an error.
	 * @since 1.0
	 */
	public function set_access( string $access_class, string $access_code, string $admin_name = NULL, string $admin_pass = NULL ) {
		// Check to see if admin access is allowed.
		if( $this->admin_check( $access_class, $admin_name, $admin_pass ) ) {
			// Check to see if access class already has a code.
			if( empty( $this->access_codes[$access_class] ) ) {
				if( $value = password_hash( $access_code, PASSWORD_DEFAULT ) ) {
					$this->access_codes[$access_class] = $value;
					$this->access_admins[$access_class] = array();
					$this->associated_users[$access_class] = array();
					$output = TRUE;		
				} else {
					$output = FALSE;
				}
			} else {
				trigger_error( "[PODE:accessVerify] Access code for access class '$access_class' cannot be set because a code already exists", E_USER_WARNING );
				$output = NULL;
			}
		} else {
			trigger_error( "[PODE:accessVerify] Access code for access class '$access_class' cannot be set because admin was rejected", E_USER_WARNING );
			$output = NULL;
		}
		// Method completion
		return $output;
	}
	/** 
	 * Method for checking whether access should be granted. If the access class does not have any users
	 * associated with it, then only the correct combination of access class and access code is required.
	 * Access is automatically granted to any user that is an admin if the correct admin name and password is
	 * provided. Access is automatically rejected if the access class has associated users but the input
	 * user is not one of them.
	 * @param string $access_class Name of the access class
	 * @param string $access_code The access code before it is processed by 'password_hash'
	 * @param string $user_name The name of the user attempting to gain access
	 * @param string $admin_pass Password of admin; indicates $user_name is for admin
	 * @return bool Returns TRUE if the user is associated with the access class (or class has no associated users) and the
	 * access code is correct, otherwise returns FALSE.
	 * @since 1.0
	 */
	public function check_access( string $access_class, string $access_code, string $user_name = NULL, string $admin_pass = NULL ): bool {
		// Check if user is associated user
		if( in_array( $user_name, $this->associated_users[$access_class] ) || empty( $this->associated_users[$access_class] ) ) {
			$output = password_verify( $access_code, $this->access_codes[$access_class] );
		} else {
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/** 
	 * Method for removing an access class; removal may only occur if admin privileges are allowed.
	 * @param string $access_class Name of the access class
	 * @param string $access_code The access code before it is processed by 'password_hash'
	 * @param string $admin_name The name of the admin
	 * @param string $admin_pass The password that verifies the admin
	 * @return bool Returns TRUE if the access class is successfully removed, otherwise returns false.
	 * @since 1.0
	 */
	public function remove_access( string $access_class, string $access_code, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Check if admin privileges are granted
		if( $this->admin_check( $access_class, $admin_name, $admin_pass ) ) {				
			$granted = password_verify( $access_code, $this->access_codes[$access_class] );
		} else {
			trigger_error( "[PODE:accessVerify] Admin privileges denied, access class could not be removed", E_USER_NOTICE );
			$granted = FALSE;
		}
		// Method completion
		if( $granted ) {
			unset( $this->access_codes[$access_class] );
			unset( $this->access_admins[$access_class] );
			unset( $this->associated_users[$access_class] );
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/** 
	 * Method for setting the admin for a given access class. If the class has already been defined, the correct
	 * access code must also be provided. Admins are automatically added as associated users.
	 * @param string $access_class Name of the access class
	 * @param string $access_code The access code before it is processed by 'password_hash'
	 * @param string $admin_name The name of the admin
	 * @param string $admin_pass The password that verifies the admin
	 * @return bool Returns true if the admin is successfully set, and otherwise returns FALSE.
	 * @since 1.0
	 */
	public function set_access_admin( string $access_class, string $access_code, string $admin_name, string $admin_pass ): bool {
		// Check to see if access class is already defined.
		if( array_key_exists( $access_class, $this->access_codes ) ) {
			// Check to see if admin does not already exist
			if( empty( $this->access_admins[$access_class] ) || !isset( $this->access_admins[$access_class][$admin_name] ) ) {
				// If access code is already defined, then correct access code must be given
				if( password_verify( $access_code, $this->access_codes[$access_class] ) ) {
					$this->access_admins[$access_class] = $this->access_admins[$access_class] ?? array();
					$this->access_admins[$access_class][$admin_name] = password_hash( $admin_pass, PASSWORD_DEFAULT );
					if( !empty( $this->associated_users[$access_class] ) ) {
						$this->associated_users[$access_class][] = $admin_name;						
					} else {
						$this->associated_users[$access_class] = array( $admin_name );
					}
					$output = TRUE;
				} else {
					trigger_error( "[PODE:accessVerify] Incorrect access code for access class '$access_class'", E_USER_WARNING );
					$output = FALSE;
				}
			} else {
				trigger_error( "[PODE:accessVerify] Admin with name '$admin_name' already exists for acces class", E_USER_WARNING );
				$output = FALSE;
			}			
		} else {
			trigger_error( "[PODE:accessVerify] The access class '$access_class' is not defined", E_USER_WARNING );
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
	/**
	 * Method for checking whether admin privileges should be given to a given access code. Admin privileges
	 * are given when the inputed user name is an admin for the inputed access class and if the inputed user
	 * password is correct. Admin status is also given if the inputed access class does not have any admins,
	 * which means that effectively any user is considered an admin for that class.
	 * @param string $access_class Name of the access class
	 * @param string $admin_name The name of the admin
	 * @param string $admin_pass The password that verifies the admin
	 * @return bool Returns TRUE if admin exists and admin privileges are allowed, otherwise FALSE is returned.
	 * @since 1.0
	 */
	protected function admin_check( string $access_class, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Initialize output variable so that default return value is FALSE
		$output = FALSE;
		// Check to see if access class has admins, if not then any admin (including NULL admin) is acceptable
		if( !empty( $this->access_admins[$access_class] ) ) {
			if( !empty( $this->access_admins[$access_class][$admin_name] ) ) {
				// Check admin password
				if( password_verify( $admin_pass, $this->access_admins[$access_class][$admin_name] ) ) {
					$output = TRUE;
				}
			}
		} else {
			$output = TRUE;
		}
		// Method completion
		return $output;
	}
	/** 
	 * Method for associating a user name with a given access class. If the access class has admins,
	 * the the correct admin name and password must be provided.
	 * @param string $access_class Name of the access class
	 * @param string $new_user The name of the user to be associated with the given access class
	 * @param string $admin_name The name of the admin
	 * @param string $admin_pass The password that verifies the admin
	 * @return bool Returns TRUE if the new user is successfully added, otherwise returns FALSE.
	 * @since 1.0
	 */
	public function add_user( string $access_class, string $new_user, string $admin_name = NULL, string $admin_pass = NULL ): bool {
		// Initialization of output variable, default is FALSE
		$output = FALSE;
		// Check to see if admin access is allowed.
		if( $this->admin_check( $access_class, $admin_name, $admin_pass ) ) {
			if( empty( $this->associated_users[$access_class] ) ) {
				$this->associated_users[$access_class] = array( $new_user );
				$output = TRUE;
			} else if( !in_array( $new_user, $this->associated_users[$access_class] ) ) {
				$this->associated_users[$access_class][] = $new_user;
				$output = TRUE;
			} else {
				trigger_error( "[PODE:accessVerify] User is already associated with access_class '$access_class'", E_USER_NOTICE );
			}
		}
		// Method completion
		return $output;
	}
	/** 
	 * Method for disassociating a user name with a given access class. If the access class has admins,
	 * the the correct admin name and password must be provided.
	 * @param string $access_class Name of the access class
	 * @param string $admin_name The name of the admin
	 * @param string $admin_pass The password that verifies the admin
	 * @param string $user_name The name of the user to be disassociated from the action class
	 * @return bool Returns TRUE if a user is successfully disassociated from the action class, returns FALSE on error,
	 * otherwise returns NULL.
	 * @since 1.0
	 */
	public function remove_user( string $access_class, string $admin_name, string $admin_pass, string $user_name ): bool {
		// Initialization of output variable, default is NULL
		$output = NULL;
		// Check to see if admin access is allowed.
		if( $this->admin_check( $access_class, $admin_name, $admin_pass ) ) {				
			if( !empty( $this->associated_users[$access_class] ) ) {
				// Search associated users via loop, unset via key.
				foreach( $this->associated_users[$access_class] as $key => $user ) {
					if( $user == $user_name ) {
						unset( $this->associated_users[$access_class][$key] );
						$output = TRUE;
					}
				}
				// Reset numerical keys for array.
				$this->associated_users[$access_class] = array_values( $this->associated_users[$access_class] );
			}
		} else {
			trigger_error( "[PODE:accessVerify] Access class '$access_class' does not have admin named '$admin_name'", E_USER_WARNING );
			$output = FALSE;
		}
		// Method completion
		return $output;
	}
}

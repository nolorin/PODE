<?php
namespace PODE;

/** 
 * 
 * @author nolorin
 * @author www.github.com/nolorin
 * @package pode_tools
 * @version 1.1
 * @since 1.0
 */
class Encryptor {
	/**
	 * All property values set after object initialization are stored in "open_vars" via magic methods
	 * @since 1.0
	 * @var array $open_vars The property array where non-encrypted data is stored.
	 */
	protected array $open_vars = array();
	/**
	 * Default encryption method
	 * @since 1.0
	 * @var string $enc_default The encryption method that is used when a variable-specific method
	 * is undefined or is not specified. Check list of available cipher methods at
	 * https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
	*/
	protected string $enc_default;
	/**
	 * Data that is encrypted and stored in a 'Encryptor' object is stored in a separate property
	 * array from the one used for unencrypted data. The array is associative, where the key is the
	 * name of the given variable and the value is the encrypted data.
	 * @since 1.0
	 * @var array $enc_vars The property array where encrypted data is stored.
	 */
	protected array $enc_vars = array();
	/**
	 * All of the encryption methods used to encrypt data are stored here in a separate associative array
	 * where the key for each item is the name of the variable stored in 'Encryptor->enc_vars'. When the
	 * value of 'Encryptor->enc_methods[var]' is equal to NULL, it means that the default encryption
	 * method should be used.
	 * @since 1.0
	 * @var array $enc_methods The property array where encryption methods are stored.
	 */
	protected array $enc_methods = array();
	/**
	 * All of the intialization vectors thare used for the different variables are stored in a separate associative 
	 * array where the key for eachitem is the name of the variable stored in 'Encryptor->enc_vars'.
	 * @since 1.0
	 * @var array $enc_methods The property array where encryption methods are stored.
	 */
	protected array $enc_ivs = array();
	/** 
	 * The constructor for Encryptor requires a default encryption which cannot be changed after
	 * object initialization.
	 * @since 1.0
	 * @param string $enc_default Default encryption method for object-data encryption. Check cipher
	 * methods at https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
	 */
	public function __construct( string $enc_default ) {
		$this->enc_default = $enc_default;
	}
	/** 
	 * Magic method '__set' automatically sets any property declaration into the 'open_vars' property
	 * array, which is reserved for all property values that are not encrypted and are not processed
	 * through the encrypting/decrypting methods.
	 * @since 1.0
	 * @param string $name Name of property variable to be defined.
	 * @param mixed $value Value of the property variable;
	 */
	public function __set( string $name, $value ) {
		$this->open_vars[$name] = $value;
	}
	/** 
	 * Magic method '__get' automatically retrieves property values from the 'open_vars' property
	 * array, which is reserved for all property values that are not encrypted and are not processed
	 * through the encrypting/decrypting methods.
	 * @since 1.0
	 * @param string $name Name of requested property variable.
	 * @return mixed Value of requested property variable.
	 */
	public function __get( string $name ) {
		if( !empty( $this->open_vars[$name] ) ) {
			$output = $this->open_vars[$name];
		} else {
			trigger_error( "[PODE:encrypt] Un-encrypted variable with the name '$name' is not defined", E_USER_WARNING );
			$output = NULL;
		}
		// Method Completion
		return $output;
	}
	/** 
	 * Magic method '__unset' automatically unsets property values from the 'open_vars' property
	 * array, which is reserved for all property values that are not encrypted and are not processed
	 * through the encrypting/decrypting methods.
	 * @since 1.0
	 * @param string $name Name of property variable to be unset.
	 */
	public function __unset( string $name ) {
		unset( $this->open_vars[$name] );
	}
	/**
	 * Magic method '__debugInfo' automatically hides the values of encryption variables.
	 * @return array Edited debugging info.
	 */
	public function __debugInfo(): array {
		$output = clone $this;
		foreach( [ 'enc_vars', 'enc_methods', 'enc_ivs' ] as $arr ) {
			foreach( $output->$arr as $name => $key ) {
				$output->$arr[$name] = str_repeat( '?', strlen( $key ) );
			}
		}
		$output = (array) $output;
		return $output;
	}
	/** 
	 * Method for encrypting storing variables using an encryption key. If no encryption method is specified,
	 * the default encryption method for the object will be used both for encryption and later decryption.
	 * @since 1.0
	 * @param string $name Variable name, which is used for reference in properties and methods.
	 * @param string $value Variable value to be encrypted.
	 * @param string $key The key that is to be used for encryption.
	 * @param string $enc_method The encryption method to be used; as a default, value is equal to NULL and the
	 * default encryption method for the object is used.
	 * @param int $opts Function options that may be used for the 'openssl' encrypting function; default value is
	 * equal to 0, which is the default for the encrypting function.
	 */
	public final function set( string $name, string $value, string $key, string $enc_method = NULL, int $opts = 0 ): bool {
		// Initialization of output variable
		$output = TRUE;
		// Establishing encryption method
		$method = !empty( $enc_method ) ? $enc_method : $this->enc_default;
		// Set initialization vector (IV)
		$strong = FALSE;
		$iv_length = openssl_cipher_iv_length( $method );
		$iv_length = $iv_length ?? 16; // If IV method not found, 16 is used as a default
		for( $i=0; !$strong || $i<1000; $i++ ) {
			$bytes = openssl_random_pseudo_bytes( $iv_length/ 2, $strong );
		}
		$iv = bin2hex( $bytes );
		// Attempt encryption, checking for errors
		try {
			$enc_value = openssl_encrypt( $value, $method, $key, $opts, $iv );
		} catch( Exception $e ) {
			trigger_error( $e->message, E_USER_ERROR );
			$output = FALSE;
		}
		// Setting encrypted values and recording encryption method and IV
		$this->enc_vars[$name] = $enc_value;
		$this->enc_methods[$name] = $method;
		$this->enc_ivs[$name] = $iv;
		// Method completion
		return $output;
	}
	/** 
	 * Method for decrypting stored variables using a decryption key, which should be the same as the encryption key
	 * used to when the variable was encrypted using 'Encryptor:set'. It is not necessary to indicate decryption
	 * method because methods are registered in 'Encryptor:set';
	 * @since 1.0
	 * @param string $name Name of the variable to be decrypted.
	 * @param string $key The key that is to be used for decryption.
	 * @param int $opts Function options that may be used for the 'openssl' decrypting function; default value is
	 * equal to 0, which is the default for the decrypting function.
	 * @return mixed Returns the decrypted value of the requested variable if the variable exists and is successfully
	 * decrypted without error, returns FALSE when decryption fails, and otherwise returns NULL.
	 */
	public final function get( string $name, string $key, int $opts = 0 ) {
		// Check that variable exists, triggering an error if it does not.
		if( !empty( $this->enc_vars[$name] ) ) {
			// Establishing decryption method
			$method = !empty( $this->enc_methods[$name] ) ? $this->enc_methods[$name] : $this->enc_default;
			// Attempt decryption, checking for errors
			try {
				$output = openssl_decrypt( $this->enc_vars[$name], $method, $key, $opts, $this->enc_ivs[$name] );		
			} catch( Exception $e ) {
				trigger_error( $e->message, E_USER_ERROR );
				$output = NULL;
			}
		} else {
			$message = "[PODE:encrypt] Encrypted variable with the name '$name' is not defined";
			trigger_error( $message, E_USER_WARNING );
			$output = NULL;
		}
		// Method Completion
		return $output;
	}
	/** 
	 * Method for unsetting encrypted variables. Encryption key is not required to unset an encrypted
	 * variable.
	 * @since 1.0
	 * @param string $name Name of the variable to be unset.
	 * @return mixed Returns the boolean TRUE if the requested variable exists and is unset, and
	 * returns NULL when the requested variable is not defined.
	 */
	public function unset( string $name ) {
		// Check to see if variable exists, otherwise an error is triggered
		if( !empty( $this->enc_vars[$name] ) ) {
			unset( $this->enc_vars[$name] );
			unset( $this->enc_methods[$name] );
			unset( $this->enc_ivs[$name] );
			$output = TRUE;
		} else {
			$message = "[PODE:encrypt] Encrypted variable with the name '$name' is not defined";
			trigger_error( $message, E_USER_WARNING );
			$output = NULL;
		}
		// Method completion
		return $output;
	}
}

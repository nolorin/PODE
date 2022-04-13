<?php
/**
 * An array containing all of the documentation texts.
 * 
 * @since 1.1
 * @return array
 */
return [
	"description" => [
		"classes" => [
			"handler" => [
				"The handler class for the PODE toolkit is designed to maximize control and security for data management within the PHP runtime environment, either within scripts, in session variables, or via serialized code.",
				"The handler class is especially useful in situations where client-side data must be processed raw. In such a case, the handler's casting methods and callable casting allow for flexibility in screening data types; the handler's system for permissions minimizes the risk of accidentally releasing incorrect data; and the handler's system for variable states provides a foundation for storing specific variable information without injecting client-side data into the workflow.",
				"The class is broken up into a core class and three traits, which are `casting`, `permissions', and `states`. Each of the component traits are designed so they can be used in outside of PODE data classes, but the `podeHandler` class requires the three states to function."
				],
				"encrypt" => [ 
					"The Encryptor class for the PODE tools package exists for the purpose of encrypting and storing string variable information inside of an object, which increases data security within the runtime environment.",
					"The Encryptor class is especially useful when using multiple coding sources whose provenance is not certain, such as plugins. It is also an extra layer of protection for handling and storing sensitive client-side data in $_SESSION variables and global scopes because it minimizes the ability of hackers to access that data via code injection, system logs, or remote hijacking of the system."
					"This class uses the OpenSSL package for encrypting and decrypting data, and it does not prescribe specific encryption methods. Initilization vectors for encryption are automatically generated and stored as a protected variable. Encryption data are not directly displayed by '__debugInfo'.",
					 "Properties are set to 'protected'and the class can be extended, but encrypting and decrypting functions are set to 'final' to prevent instances where a dummy class is created as a Trojan horse for accessing  protected information."
				],
				"access" => [
					"The Access class for the PODE toolkit is designed to handle access across multiple scopesand with multiple users.",
					"This class is useful for managing permissions and can be an efficient API between backend coding and database servers. It can also be useful as an authentication keychain when multiple access levels or authenticating users are being processed in the same session or using seralized code.",
					"The class is not extendable so as to prevent malicious code compromising the verification system, which relies on boolean function returns for its methods.",
					"Each scope is referenced by an 'access class', which has a set access code. Each access class may have a whitelist of associated users, so that any users who are not on the whitelist are automatically denied access.",
					"Additionally, access classes may have one or more administrators who are identified by a password; administrators may change the access code for their access class, add users to that class's whitelist, and remove users from the whiteless.",
					"This class uses the PHP core functions 'password_hash' and 'password_verify' for hashing access codes and checking access codes and admin passwords against previously recorded hashes. The hashing algorithm that is used for both functions is PASSWORD_DEFAULT."

				]
			],
		"traits" => [
			"Casting" => [ 
				"The Casting trait is designed to give an object more options in setting type casts for its properties. This trait allows properties to have one or more type casts among the PHP basic data types.",
				"It also allows for casting callback functions, which are functions that can be run with arguments to determine whether a variable can be set as property to the object. The return value of a casting callback function is interpetted as either TRUE or FALSE by Casting methods."
			],
			"Permissions" => [ 
				"The Permissions traits contains properties and methods for managing user access to data that is contained  within the PODE object. Users can be assigned permissions, which may take the form of a symbolic string or a callback function which may be called to return a symbolic string. Permissions callback functions may or may not take arguments.",
				"A single administrator identified by a single password has the power to manage both users and permissions. If an administrator has not been set, then the management of users and permissions is unrestricted.",
				"Changing or reseting the administrator may only be done if the property variable 'pode_admin_regenerate' is set to TRUE before any admin is assigned to the object or if the current admin's name and password invoked when changing the property via the method 'pode_admin_regenerate'."
			],
			"States" => [ 
				"The States trait is designed to provide discrete, adaptive character states that can be used to describe variables. Each variable must be assigned one or more allowed states before a current state can be assigned. A variable may be assigned a current state so long as that state is one of the pre-defined, allowed states assigned to that variable."
			]
		]
	]
];

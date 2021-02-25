The PHP Object-Data Encapsulation (PODE) toolkit is a set of classes and traits that increase the security and control of object-data handling in PHP programs. PODE is especially useful for multi-sourced platforms such as WordPress and Joomla, where the provenance of individual plugins is unknown and unverified to other plugin developers. PODE is also an effective additional layer of security for websites that handle sensitive client data, including ecommerce sites, because it allows the server to store sensitive information in session variables or in serialized form without exposing that data to theft in the event of a security breach. The toolkit also can be used to enhance and streamline development by enforcing powerful but adaptable type-casting filters and data permissions within code, which are beneficial features in large projects involving many developers with varying degrees of coordination.

The following are the class and trait descriptions included within the in-code documentation:

[podeEncrypt]

The encrypt class for the PODE tools package exists for the purpose of encrypting and  storing string variable information inside of an object, which increases data security within the runtime environment The 'podeEncrypt' class is especially useful when using multiple coding sources whose provenance is not certain, such as plugins. It is also an extra layer of protection for handling and storing sensitive client-side data in $_SESSION variables and global scopes because it minimizes the ability of hackers to access that data via code injection, system logs, or remote hijacking of the system.

[podeAccessVerify]

The access verify class for the PODE toolkit is designed to handle access across multiple scopes and with multiple users. This class is useful for managing permissions and can be an efficient API between backend coding and database servers. It can also be useful as an authentication keychain when multiple access levels or authenticating users are being processed in the same session or using seralized code. The class is not extendable so as to prevent malicious code compromising the verification system, which relies on boolean function returns for its methods.

[podeHandler] 

The handler class for the PODE toolkit is designed to maximize control and security for data management within the PHP runtime environment, either within scripts, in session variables, or via serialized code. The handler class is especially useful in situations where client-side data must be processed raw. In such a case, the handler's casting methods and callable casting allow for flexibility in screening data types; the handler's system for permissions minimizes the risk of accidentally releasing incorrect data; and the handler's system for variable states provides a foundation for storing specific variable information without injecting client-side data into the workflow.

[podeCasting]

The PODE casting trait is designed to give an object more options in setting type casts for its properties. This trait allows properties to have one or more type casts among the PHP basic data types. It also allows for casting callback functions, which are functions that can be run with arguments to determine whether a variable can be set as property to the object. The return value of a casting callback function is interpetted as either TRUE or FALSE by podeCasting methods.

[podeStates]

The PODE states trait is designed to provide discrete, adaptive character states that can be used to describe variables. Each variable must be assigned one or more allowed states before a current state can be assigned. A variable may be assigned a current state so long as that state is one of the pre-defined, allowed states assigned to that variable.

[podePermissions]

The PODE permissions traits contains properties and methods for managing user access to data that is contained  within the PODE object. Users can be assigned permissions, which may take the form of a symbolic string or a callback function which may be called to return a symbolic string. Permissions callback functions may or may not take arguments. A single administrator identified by a single password has the power to manage both users  and permissions. If an administrator has not been set, then the management of users and permissions is unrestricted. Changing or reseting the administrator may only be done if the property variable 'pode_admin_regenerate' is set to TRUE before any admin is assigned to the object or if the current admin's name and password invoked when changing the property via the method 'pode_admin_regenerate'.

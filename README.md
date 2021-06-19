
 	To install this site, please add the following in your wp-config.php file. 
 
 	/**
	 * Tell WordPress to load from local wp-content, and not vendor wp.
	 * @var bool
	 */
	define('WP_CONTENT_DIR', dirname(__FILE__) . '/wp-content');
	
	/**
	 * Tell WordPress to load assets from wp-content and not vendor wp. 
	 * @var bool
	 */
	define('WP_CONTENT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/wp-content');
	
	/**
	 * Tell WordPress not to update anything. 
	 * @var bool
	 */
	 define ('AUTOMATIC_UPDATER_DISABLED', true );
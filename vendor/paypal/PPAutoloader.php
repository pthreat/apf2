<?php
	 /**
      * Basic class-map auto loader generated by install.php.
	  * Do not modify.
	  */
	 class PPAutoloader {
	 	private static $map = array (
  'configuration' => 'Configuration.php',
);

		public static function loadClass($class) {
	        $class = strtolower(trim($class, '\\'));

    	    if (isset(self::$map[$class])) {
            	require dirname(__FILE__) . '/' . self::$map[$class];
        	}
    	}

		public static function register() {
	        spl_autoload_register(array(__CLASS__, 'loadClass'), true);
    	}
}
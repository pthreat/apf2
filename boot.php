<?php 

		if(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."Kernel.class.php")){

			$class	=	dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."Kernel.class.php";

			throw new \Exception("Core Kernel class couldn't be found in $class");

		}

		require dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."Kernel.class.php";

		if(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."web".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."Kernel.class.php")){

			$class	=	dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."web".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."Kernel.class.php";

			throw new \Exception("Core Web Kernel class couldn't be found in $class");

		}

		require dirname(__FILE__).DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."web".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."Kernel.class.php";

<?php

	namespace apf\console{

		use apf\type\base\Str		as	StringType;
		use apf\type\base\Vector	as	VectorType;
		use apf\type\util\base\Str	as	StringUtil;

		class Command{

			public static function run($cmd,$parameters=['autocast'=>TRUE]){

			  $cmd	=	\escapeshellcmd($cmd);

			  if(function_exists("pcntl_exec")){
				  die("YES");
			  }

			  $whichCmd	=	trim(exec("which $cmd"));

			  if(empty($whichCmd)){

				  throw new \InvalidArgumentException("Invalid command $cmd");

			  }

			  return StringType::cast(shell_exec($cmd),$parameters);

		  }

		}

	}

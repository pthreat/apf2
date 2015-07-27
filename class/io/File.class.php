<?php

	namespace apf\io;
	
	$os			=	preg_match('/win/i',php_uname('s'))	?	'win'	:	'unix';
	$original	=	sprintf('\apf\io\os\%s\File',$os);
	$alias		=	'apf\io\File';

	class_alias($original,$alias,$autoload=TRUE);


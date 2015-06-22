<?php

	ini_set("display_errors","On");
	error_reporting(E_ALL);


	require "tests/include.php";

	use apf\core\Log			as	Log;
	use apf\type\base\Str	as	StringType;
	use apf\type\base\Char	as	CharType;

	$log	=	new Log();
	$log->setNoPrefix();

	$jp	=	'こんにちは今日はどうですか';
	$test	=	StringType::cast($jp,['trim'=>TRUE]);


	$log->debug($jp,'test');
	$log->error($jp,'test');
	$log->emergency($jp,'test');
	$log->success($jp,'test');
	$log->warning($jp,'test');
	$log->info("Multibyte string: $jp");

	$str	=	StringType::cast($jp);
	$log->line();
	$log->setPrepend('APF> ');
	$log->info($str);

	//Apollo string type
	foreach($str as $v){

		$log->info($v);

	}

	$log->setPrepend('');
	//Native PHP Demo
	$log->debug('PHP String type');
	$log->line();

	$log->setPrepend('PHP> ');
	$chars	=	str_split($jp);
	foreach($chars as $ch){
		$log->warning($ch,['fromEncoding'=>'UTF-8']);
	}

	//Standard class, no __toString, no traversable interface
	class test{
		public function blah(){
		}
	}

	class traverse implements \Iterator{

		use apf\traits\traversable;

		public function __construct(){

			$this->value	=	[1,2,3,4,5];

		}

	}

	class printable{

		public function __toString(){
			return "I'm printable";
		}

	}

	class printable2{

		public function toString($parameters=NULL){
			return StringType::cast('Another string method',$parameters);
		}

	}

	class printable3{

		public function toString(){
			return "I will not be printed";
		}

		public function __toString(){
			return "But I will!";
		}

	}

	$int			=	9000;
	$double		=	9e99;
	$resource	=	fopen('/etc/passwd','r');

	$someArray	=	['hello','world','array','to','string','auto','conversion'];

	$log->debug("\nInt to string conversion\n");
	$log->info(StringType::cast($int)->valueOf());

	$log->debug("\nDouble to string conversion\n");
	$log->info(StringType::cast($double)->valueOf());

	$log->debug("\nResource to string conversion\n");
	$log->info(StringType::cast($resource)->valueOf());
	fclose($resource);

	$log->debug("\nArray to string conversion\n");

	$str			=	StringType::cast($someArray,['separator'=>'&']);
	$log->info($str->valueOf());

	$log->debug("\nStandard class to string [NO __toString or toString]\n");
	$str			=	StringType::cast(new test());
	$log->info($str->valueOf());

	$log->debug("\nTraversable class to string [NO __toString or toString]\n");
	$str			=	StringType::cast(new traverse(),['separator'=>'&']);
	$log->info($str->valueOf());

	$log->debug("\nStandard class with __toString\n");
	$str			=	StringType::cast(new printable());
	$log->info($str->valueOf());

	$log->debug("\nStandard class with toString [NO MAGIC __toString]\n");
	$str			=	StringType::cast(new printable2());
	$log->info($str->valueOf());

	$log->debug("\nStandard class with __toString and toString [__toString takes precedence]\n");
	$str			=	StringType::cast(new printable3());
	$log->info($str->valueOf());

	$log->debug("\nGet character collection\n");
	$collection	=	$str->toArray();
	$collection[]=CharType::cast('1');
	$log->info($collection);

	$collection[]	=	Array();

	var_dump($a);


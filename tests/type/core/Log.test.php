<?php

	ini_set("display_errors","On");
	error_reporting(E_ALL);


	require "tests/include.php";

	use apf\core\Log			as	Log;
	use apf\type\base\Str	as	StringType;

	$log	=	new Log();

	$log->log('Gianni maggiora años tiene %d años y vive en %s','21','CABA',['color'=>'blue','fromEncoding'=>'UTF-8','toEncoding'=>'ASCII//TRANSLIT']);
	$str	=	StringType::cast('gianni maggiora tiene 21 años');
	echo $str->toSlug();
	die();


	
	$log->info('Info');
	$log->debug('Debug');
	$log->error('Error');
	$log->emergency('Emergency');
	$log->success('Success');
	$log->warning('Warning');

	$log->log('custom',['color'=>'blue']);
	$log->info("String templating, argument color=>red provided");
	$log->log('Template %s %s %s ','<templating string>','templating','5',['color'=>'red']);

	$log->info('Wrong templating arguments provided, in this case vsprintf expects 2 arguments');

	try{

		$log->info('Wrong templating %s %s');

	}catch(\Exception $e){

		$log->emergency($e->getMessage());

	}

	$log->info("Provide an erroneous encoding such as \"lalala\"");

	try{

		$log->info("Log an array with templating %s",[1,2,3],['fromEncoding'=>'lalala']);

	}catch(\Exception $e){

		$log->emergency($e);

	}

	class test{
	}

	$log->info("Log an object with templating: %s",new test());


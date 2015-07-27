<?php

	require "tests/include.php";

	use apf\type\base\StrDisk;
	use apf\type\util\common\Variable	as	VarUtil;
	use apf\core\Log;

	$log		=	new Log();

	$string	=	'aあ こんにちはaaakanaa';
	$string	=	'abcdefghij';
	$string	.=	chr(30);
	$log->info("Base string: $string");
	$str		=	new StrDisk($string);

	$log->info("Clip string FROM 'a' to 'c': %s",$str->clip(['charStart'=>'a','charEnd'=>'c']));
	$log->info("Clip string FROM 'a' to 'f' but CHOP ends: %s",$str->clip(['charStart'=>'a','charEnd'=>'f','chop'=>TRUE]));
	$log->info('Cut First (start from 0 to "d" %s)',$str->cutFirst(['delimiter'=>'d']));

	$log->info("Test if control characters work like they should (in this case chr(30)");
	$char		=	$str->substr($str->strpos(chr(30)))[0]->ord()->valueOf();

	if($char==30){
		$log->success("YES");
	}else{
		$log->error("ERROR!");
	}

	$offsets	=	[
						[0,1],
						[0,2],
						[0,3],
						[0,4],
						[1,2],
						[1,3],
						[1,4],
						[2,1],
						[2,2],
						[3,3],
						[3,5],
						[3,2],
						[-1,3],
						[-1,10],
						[-50,1],
						[-1,-1],
						[-1,-2],
						[-1,-3],
						[-1,-4],
						[-4,-4],
						[-2,-3],
						[-5,-1],
						[-6,-2],
						[-2,-2],
						[0,0],
						[1],
						[2],
						[3],
						[4],
						[5],
						[6],
						[20],
						[-1],
						[-2],
						[-3],
						[-4],
						[-5],
						[-6],
						[-7],
						[-8]
	];

	foreach($offsets as $o){

		if(isset($o[1])&&isset($o[0])){

			$start	=	$o[0];
			$length	=	$o[1];

		}else{
			$start	=	$o[0];
			$length	=	NULL;
		}

		$log->info("Try to substring, starting from $start to $length");

		if(!is_null($length)){

			$apf	=	$str->substr(['start'=>$start,'length'=>$length]);
			$php	=	substr($string,$start,$length);

			$log->warning("PHP:%s",$php);
			$log->debug("APF:%s","$apf");

		}else{

			$apf	=	$str->substr($start);
			$php	=	substr($string,$start);

			$log->warning("PHP:%s",substr($string,$start));
			$log->debug("APF:%s",$str->substr($start));

		}

		if((string)$apf==(string)$php){

			$log->success("OK");

		}else{

			$log->error("ERROR!");

		}

	}



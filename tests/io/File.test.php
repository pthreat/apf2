<?php

	require "tests/include.php";

	use apf\io\File as File;
	use apf\core\Log;

	$log		=	new Log();

	$log->info("Make a temporary file");
	$file		=	new File(['tmp'=>TRUE]);

	$file		=	__FILE__;
	$log->info("Make a file instance of $file in readable mode");
	$file		=	new File($file);

	$log->info("Get file owner");
	$owner	=	$file->getOwner();
	$log->debug("Owner: $owner");

	$log->info("Get file group");
	$group	=	$file->getGroup();
	$log->debug("Group: $group");

	$log->info("Do an 'ls' command");
	$log->debug($file->ls());

	$log->info("Read the file through foreach line by line");

	foreach($file as $line){

		$log->debug("Foreach: %s",$line);

	}
	
	$log->info("Get file size");
	$log->debug("File size %d bytes",$file->getSize());

	$file	=	'a';
	$log->info("Open $file");

	$file	=	new File('a');

	$log->info("Get file owner: %s",$file->getOwner());
	$log->info("Get file group: %s",$file->getGroup());

	$file->chmod('rwx===rw=');
	echo $file->ls()."\n";
	$file->chmod('r--------');
	echo $file->ls()."\n";




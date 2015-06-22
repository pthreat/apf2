<?php

	require "tests/include.php";

	use apf\core\OS;
	use apf\core\Log;
	use apf\core\cache\adapter\File	as	FileCache;

	$log		=	new Log();
	$os		=	OS::getInstance();
	$cache	=	new FileCache();
	$cache->setLog($log);
	$cache->store('test',function(){return OS::getInstance()->cpuInfo();},60);
	$cache->store('test2',function(){return OS::getInstance()->cpuInfo();},60);
	$cache->store('test3',function(){return OS::getInstance()->cpuInfo();},60);
	$cache->store('test4',function(){return OS::getInstance()->cpuInfo();},60);
	$cache->store('test5',function(){return OS::getInstance()->cpuInfo();},60);
	echo $cache->get('test');
	echo $cache->info();
	$log->debug("List cache entries");

	foreach($cache->listEntries() as $entry){
		echo $entry."\n";
	}

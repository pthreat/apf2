<?php

	namespace apf\core\os{

		use apf\io\File;
		use apf\hw\CPU							as CPU;
		use apf\hw\Memory						as	Memory;
		use apf\hw\disk\Partition;
		use apf\hw\collection\CPU			as	CPUCollection;
		use apf\type\util\base\Str			as StringUtil;

		class Linux extends Common{

			public function cpuInfo(){

				$cpu				=	new \stdClass();
				$cpuCollection	=	new CPUCollection();
				$number			=	1;

				$file				=	new File('/proc/cpuinfo');
				$handler			=	$file->getHandler(['mode'=>'r']);

				while(($line = $handler->fgets())!==FALSE){

					$line	=	$line->keyValuePair(':');

					$key	=	StringUtil::toCamelCase($line['key']);

					if(empty($key)){

						if(!empty($cpu)){
	
							$objCPU	=	(new CPU($cpu))
							->setFlags($cpu->flags)
							->setMhz($cpu->cpuMhz)
							->setNumber($number)
							->setCacheSize($cpu->cacheSize)
							->setAmountOfCores($cpu->cpuCores)
							->setFPU($cpu->fpu)
							->setModel($cpu->modelName);

							$cpuCollection->add($objCPU);

						}

						$number++;

						continue;

					}

					$cpu->$key	=	StringUtil::trim($line['value']);

				}

				return $cpuCollection;

			}

			public function memInfo(){

				$file				=	new File('/proc/meminfo');
				$handler			=	$file->getHandler(['mode'=>'r']);

				$memory			=	new \stdClass();

				while(($line = $handler->fgets())!==FALSE){

					$line	=	$line->keyValuePair(':');
					$key	=	StringUtil::toCamelCase($line['key'],'[_\s]');

					$memory->$key	=	$line['value'];

				}

				return (new Memory((Array)$memory))
				->setSwapTotal($memory->swapTotal)
				->setSwapFree($memory->swapFree)
				->setRAMTotal($memory->memTotal)
				->setRAMFree($memory->memFree);

			}

			public function partition($name){

				return new Partition($name);

			}

			public function configure(\apf\iface\Config $config){
			}

		}

	}

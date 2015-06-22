<?php

	namespace apf\apf\db\mysql5 {

		class DbBuilder {

			private	$xmlFile		=	NULL;
			private	$xmlParser		=	NULL;
			private	$config			=	NULL;
			private	$logger			=	NULL;
			private	$adapter		=	NULL;
			private	$mLoader		=	NULL;


			public function __construct(Array $config,\apf\adapter &$adapter,\apf\core\ModuleLoader &$mLoader,\apf\iface\Log &$log){

				$this->xmlFile		=	new \apf\core\File($config["makedb"]);
				$this->xmlParser		=	new \apf\parser\RevEngXml($this->xmlFile);

				$appConfig				=	$this->xmlParser->getConfig();

				$this->config			=	array_merge($appConfig,$config);

				$this->setLog($log);
				$this->adapter	=	$adapter;
				$this->mLoader		=	$mLoader;
				
				$this->log("Reverse database engineering engine started!",0,"light_green");

				$this->makeDb();
	
			}

			public function setLog(\apf\iface\Log &$logger){

				$logger->setPrepend('['.__CLASS__.']');
				$this->logger	=	$logger;

			}


			private function log($msg=NULL, $color="white", $level=0, $toFile=FALSE) {

				if (isset($this->config["log-all"])) {
					$toFile = TRUE;
				}

				if (!is_null($this->logger)) {

					$this->logger->setPrepend('[' . __CLASS__ . ']');
					$this->logger->log($msg, $color, $level, $toFile);
					return TRUE;

				}

				return FALSE;

			}

			public function setConfig(Array $config){
				$this->config	=	$config;
			}

			private function getResult($res){

				foreach($res as $result){

					if($result["parserType"]!=="sqli"){
						continue;
					}

					if(is_array($result["return"])){

						foreach($result["return"] as $r){

							if($result!=''){
								return $r;
							}

						}

					}else{

						return $result["return"];

					}

				}

			}

			public function makeDb(){
		
				if(!class_exists("MySQLi")){
					throw (new \Exception("Couldnt make MySQLDBAdapter instance, make sure you have the mysqli extension installed"));
				}

				$MySQLi	=	new \apf\apf\db\MySQLDbAdapter($this->logger,$this->config["dbhost"],"root",$this->config["dbpass"]);

				if($this->config["verbose"]){

					$MySQLi->setVerbose();

				}

				$this->log("Parsing XML File {$this->xmlFile}",0,"light_cyan");

				$plugins		=	$this->xmlParser->getPlugins();

				foreach($plugins as $plugin){

					$this->log("Trying to reverse enginner database with plugin $plugin[class] ...");

					if(!$plugin["vulnerable"]){

						$this->log("Not vulnerable to this plugin, skipping",1,"yellow");

					}

					$sqliResult			=	new \apf\plugin\sqli\SqliResult();
					
					$pluginParameters	=	$this->xmlParser->getPluginConfig($plugin["class"]);

					if(sizeof($pluginParameters)){

						$sqliResult->setPluginParameters($pluginParameters);

					}


					/**********************************************************/
					//Awful ... should fix this later making a new method in the 
					//plugin loader such as getInstanceByClassName

					$pluginTData	=	explode("\\",$plugin["class"]);
					$pType			=	$pluginTData[2];
					$pSubType		=	$pluginTData[3];
					$pName			=	$pluginTData[4];

					/**********************************************************/

					$uriData			=	$this->xmlParser->getPluginUri($plugin["class"]);
					

					$this->adapter->setUri(new \apf\parser\Uri($uriData["uri"]));

					foreach($uriData["parameters"] as $key=>$value){

						$sqliResult->setRequestVariable($value["name"],$value["value"],$value["affected"]);

					}

					$objPlugin	=	$this->mLoader->getPluginInstance($pType,$pSubType,$pName,$this->adapter,$this->logger);

					if(in_array("re-run",array_keys($this->config))){

						$objPlugin->run();

					}else{

						$objPlugin->setSQLiResult($sqliResult);

					}

					$dbPrefix	=	"apf_";

					$schemas		=	$this->xmlParser->getPluginSchemas($plugin["class"]);

					$this->log("Found ".sizeof($schemas)." schemas for this plugin",0,"light_cyan");

					if(!sizeof($schemas)){

						$this->log("No schemas found for this plugin in the XML file ...",1,"red");
						continue;

					}

					foreach($schemas as $schema){

						$schemaName			=	$schema["name"];

						$this->log("Processing schema \"$schemaName\"",0,"yellow");

						$localSchemaName	=	$dbPrefix.$schemaName;

						$this->createDatabase($MySQLi,$localSchemaName);

						$MySQLi->select_db($localSchemaName);

						$schemaTables	=	$this->xmlParser->getSchemaTables($plugin["class"],$schemaName);

						if(array_key_exists("interactive",$this->config)){

							$tables	=	Array();

							foreach($schemaTables as $index=>$schemaTable){
								$tables[$index]	=	$schemaTable["name"];
							}

							$selectedIndexes	=	interactive($this->logger,$tables);

							foreach($tables as $k=>$v){

								if(!in_array($k,$selectedIndexes)){
									unset($schemaTables[$k]);
								}

							}

						}

						foreach($schemaTables as $index=>$schemaTable){

							$tableStructure	=	$schemaTable["structure"];
							$tableColumns		=	$schemaTable["columns"];
						
							if(isset($selectedIndexes)){

								if(!in_array($index,$tables)){
									$this->log("Omitting table $schemaTable[name]!",0,"yellow");
									continue;
								}
	
							}

							$this->log("Creating table $schemaTable[name] ...",0,"yellow");

							if($this->createTable($MySQLi,$schemaTable)!==TRUE){

								throw(new \Exception("Couldnt create table $localSchemaTableName ".$MySQLi->errno.':'.$MySQLi->error));
							}

							$count	=	$this->getResult($objPlugin->count($tableColumns[0]["name"],$schemaTable["name"]));

							if($count==0){

								$this->log("Found $count registers in table $schemaTable[name], processing following table",0,"red");
									
								continue;

							}

							$this->log("Found $count registers in table $schemaTable[name]",0,"light_cyan");

							$limit	=	Array();

						
							$asciiFieldSeparator	=	'|';	
							$hexFieldSeparator	=	\String::hexEncode($asciiFieldSeparator);

							for($i=0;$i<$count;$i++){

								$limit	=	Array($i,1);

								$select		=	Array();
								$colInsert	=	Array();

								foreach($schemaTable["columns"] as $col){

									$colInsert[]	=	$col["name"];
									$select[]		=	$col["name"];
									$select[]		=	$hexFieldSeparator;

								}

								$select	=	implode(",",$select);

								$values	=	$this->getResult($objPlugin->query($select,$schemaTable["name"],Array(),Array(),Array(),$limit));

								$values	=	trim($values,$asciiFieldSeparator);

								$values	=	explode($asciiFieldSeparator,$values);

								if(!$this->insertRegisters($MySQLi,$schemaTable["name"],$colInsert,$values)){

									$this->logger->log("Couldnt insert registers on $localSchemaTableName table!".$MySQLi->errno.':'.$MySQLi->error,0,"red");

								}

							}

							$limit	=	Array();

						}

					}

				}

			}

			public function createDatabase(\MySQLi &$sqli,$schemaName){

				$sql	=	"CREATE DATABASE IF NOT EXISTS $schemaName";
				return $sqli->query($sql);

			}

			public function createTable(\MySQLi &$sqli,Array $tableData){

				$tableName			=	$tableData["structure"]["name"];
				$tableEngine		=	$tableData["structure"]["engine"];
				$tableCollation	=	$tableData["structure"]["collation"];

				$tableColumns		=	$tableData["columns"];

				$sql	=	NULL;
				$sql	=	"CREATE TABLE IF NOT EXISTS $tableName(";

				$columns	=	Array();

				foreach($tableColumns as $column){

					$columns[]=$column["name"].' '.$column["structure"]["type"];

				}

				$columns		=	implode(',',$columns);
				$sql			=	"CREATE TABLE IF NOT EXISTS $tableName(".$columns.")";


				if(!empty($tableEngine)){
					$sql	.=	"ENGINE $tableEngine";
				}

				if(!empty($tableCollation)){
					$sql	.=	" COLLATE $tableCollation";
				}

				$truncate	=	"TRUNCATE TABLE $tableName";
				
				return ($sqli->query($sql) && $sqli->query($truncate));

			}

			public function insertRegisters(\MySQLi &$sqli,$tableName,Array $columns,Array $registers){
			
				$sql		=	"INSERT INTO $tableName SET ";
				$result	=	@array_combine($columns,$registers);

				if(!$result){

					//FIX ME
					$registers	=	array_pad($registers,sizeof($columns),'-');
					$result		=	array_combine($columns,$registers);
					$this->log("WARNING! INSERTING PADDED VALUES, THIS DATA IS NOT ACCURATE!",1,"red");

				}

				foreach($result as $colName=>$colVal){
					$sql.="$colName='".$sqli->real_escape_string($colVal)."',";
				}

				$sql=substr($sql,0,-1);
				return $sqli->query($sql);

			}

		}

	}

?>

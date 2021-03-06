<?php

	namespace apf\db\mysql5{

			abstract class Query{

				protected	$table						=	NULL;
				protected	$fieldDelimiter			=	',';
				protected	$fieldEqualityChar		=	'=';
				protected	$space						=	" ";	//This could be aswell /**/ for evading ids's
				protected	$commentOpen				=	"/*";
				protected	$commentClose				=	"*/";
				protected	$fields						=	Array();
				protected	$result						=	NULL;
				protected	$params						=	NULL;
				protected	$error						=	NULL;
				protected	$adapter		   			=	NULL;

            protected   $sqlArray               =	Array(
                                                         "fields"=>Array(),
                                                         "where"=>NULL,
                                                         "having"=>Array(),
                                                         "group"=>Array(),
                                                         "order"=>Array(),
                                                         "limit"=>Array(),
                                                         "offset"=>NULL,
                                                         "union"=>NULL,
                                                         "join"=>Array()
            );

				abstract public function getResult();
				abstract public function getSQL();

				public function __construct($table=NULL,$params=NULL){

					$this->adapter	=	Adapter::getInstance($params);
					$this->params	=	$params;

					if(is_string($table)){

						return $this->setTable(new Table($table));

					}elseif(!is_null($table)){

						$this->setTable($table);

					}

				}

				public function startTransaction(){

					return $this->adapter->query('start transaction');

				}

				public function commit(){

					return $this->adapter->query('commit');

				}

				public function rollback(){

					return $this->adapter->query('rollback');

				}

				public function where(Array $conditions){

					foreach($conditions as $key=>$value){

						$where	=	'';

						if(!is_array($value)){

							throw(new \Exception("Query format error, given array subelement is not an array"));

						}
						
						if(!isset($value["field"])){

							$value["field"]='';

						}else{

							$value["field"]	=	$this->adapter->real_escape_string($value["field"]);

						}

						if(isset($value["operator"])){

							$value["operator"]	=	strtoupper($this->adapter->real_escape_string($value["operator"]));

						}elseif(!isset($value["operator"])&&isset($value["value"])){
							
							$value["operator"]	=	'=';

						}else{

							$value["operator"]	=	$this->space;

						}

						if(isset($value["begin_enclose"])){

							$where	=	'('.$value["field"].$this->space.$value["operator"].$this->space;

						}else{

							$where	=	$value["field"].$this->space.$value["operator"].$this->space;

						}

						if(!isset($value["value"])){

							$value["value"]='';

						}else{
							
							switch(strtolower($value["operator"])){

								case "between":

									if(!is_array($value["value"])){

										throw(new \Exception("Debe utilizar un Array(min=>minimo,max=>maximo) para utilizar between"));
									}

									foreach($value["value"] as &$v){	

										if(is_array($v)&&array_key_exists("quote",$v)&&$v["quote"]==FALSE){
											$v	=	$this->adapter->real_escape_string($v);
										}else{
											$v	=	"'".$this->adapter->real_escape_string($v)."'";
										}

									}

									$where.=implode($value["value"]," AND ");

								break;

								case 'beginsLike':

									$where.="'%".$this->adapter->real_escape_string($value["value"])."'";

								break;

								case 'endsLike':

									$where.="'".$this->adapter->real_escape_string($value["value"])."%'";
								break;

								case "like":

									$where.="'%".$this->adapter->real_escape_string($value["value"])."%'";

								break;

								case "in":
								case "not in":

									if(is_array($value["value"])){

										foreach($value["value"] as &$v){

											$v	=	"'".$this->adapter->real_escape_string($v)."'";
										
										}

										$where.='('.implode($value["value"],',').')';

									}else{

										if(!is_a($value["value"],"\\apf\db\\mysql5\\Select")){

											throw(new \Exception("El valor, cuando se utiliza IN o NOT IN, tiene que ser un Array o un objeto de tipo Select"));
										
										}

										$where.='('.$value["value"]->getSQL().')';

									}

								break;

								default:

									if(!is_bool($value["value"])){

										if(array_key_exists("quote",$value)){

											$quote	=	($value["quote"])	?	"'"	:	'';

										}else{

											$quote	=	"'";

										}

									}else{

										$quote = '';
										$value["value"]	=	$value["value"] ? 'TRUE' : 'FALSE';

									}

									$where.=$quote.$this->adapter->real_escape_string($value["value"]).$quote;

								break;

							 }

						}

						if(isset($value["end_enclose"])){

							$this->sqlArray["where"].=	$where.')';

						}else{

							$this->sqlArray["where"].=	$where;

						}

					}

					return $this;

				}


				public function getParams(){

					return $this->params;

				}

				public function setSpaceCharacter($space){

					$this->space	=	$space;

				}

				public function getSpaceCharacter(){

					return $this->space;

				}	

				public function setCommentOpen($commentOpen){

					$this->commentOpen	=	$commentOpen;

				}

				public function setCommentClose($commentClose){

					$this->commentClose	=	$commentClose;

				}

				public function setFieldEqualityCharacter($equalityCharacter){

					$this->fieldEqualityCharacter	=	$equalityCharacter;

				}

				public function setTable(Table $table){

					$tableName	=	$table->getName();

					if(empty($tableName)){

						throw(new \Exception("Table name can't be empty"));

					}

					$this->table	=	$table;

				}

				public function getTable(){

					return $this->table;

				}

				public function escapeArrayValues(Array &$array){

					$tmpArray	=	Array();

					foreach($array as $key=>&$value){

						if(!empty($value)){

							$value	=	$this->adapter->real_escape_string($value);

						}

					}

				}

				public function reset(){

					$this->sql	=	array();	

				}

				public function execute($map=NULL,$smart=TRUE){

					$sql		=	sprintf("%s",$this);

					if($this->error){

						throw(new \Exception($this->error));

					}

					$log	=	$this->adapter->getLog();

					if(defined("LOG_SQL")||$this->adapter->getVerbose()){

						$log	=	new \apf\core\Log();
						$log->setEcho(TRUE);
						$log->log($sql,0);

					}

					$this->result	=	$this->adapter->query($sql);

					if(!$this->result){

						throw(new \Exception("QUERY FAILED: $sql (".$this->adapter->error.' | '.$this->adapter->errno.')'));

					}

					return $this->getResult($map,$smart);

				}

				public function getQueryResult(){

					return $this->result;

				}

				public function addField($field,$value){


					if(is_bool($value)){

						$this->fields[$field]	=	$value ? 'TRUE' : 'FALSE';
						return;

					}
					
					if(is_null($value)){

						$this->fields[$field]	=	'NULL';
						return;

					}


					if(is_array($value)){

						if(isset($value["quote"])){

							if($value["quote"]){
								
								$this->fields[$field]	=	"'".$this->adapter->real_escape_string($value["value"])."'";

							}else{

								$this->fields[$field]	=	$this->adapter->real_escape_string($value["value"]);

							}

						}else{

							$this->fields[$field]="'".$this->adapter->real_escape_string($value["value"])."'";

						}

					}

					//if(is_string($value)){

						$this->fields[$field]	=	"'".$this->adapter->real_escape_string($value)."'";
						return;

					//}

				}

				protected function getFields(){

					$fields	=	Array();

					foreach($this->fields as $field=>$value){

						$fields[]	=	'`'.$this->adapter->real_escape_string($field).'`='.$value;

					}

					return implode(',',$fields);

				}


				public function fields(Array $fields,$quote=TRUE){

					foreach($fields as $key=>$value){

						$this->addField($key,$value);

					}

				}

				public function join(Join $join){

					$this->sqlArray["join"][]	=	$join;

				}

				public function __set($var,$value){

					return $this->addField($var,$value);
					
				}

				public function __toString(){

					try{

						$sql	=	$this->getSQL();
						$this->error	=	FALSE;
						return $sql;

					}catch(\Exception $e){

						$this->error	=	$e->getMessage();
						return '';

					}

				}

			}

		}
?>

<?php

	namespace apf\db\mysql5\select{

		class Result implements \Iterator,\ArrayAccess{

			private	$select		=	NULL;
			private	$result		=	NULL;
			private	$currentRow	=	NULL;
			private	$position	=	NULL;
			private	$map			=	NULL;

			public function __construct(\apf\db\mysql5\Select $select,$map=NULL){

				$this->select	=	$select;
				$this->result	=	$select->getQueryResult();
				$this->map		=	$map;

			}

			public function getFoundRows(){

				return $this->select->getFoundRows();

			}

			public function getSelect(){

				return $this->select;

			}

			private function map(Row $data=NULL){

				if(is_null($this->map)){

					return $data;

				}

				//execute(some\class);
				/////////////////////////////////////////

				if(is_string($this->map)){

					if(!class_exists($this->map)){

						$msg	=	"Class \"$this->map\" doesn't exists. Maybe you forgot to ".
									"require/include the class?";

						throw(new \Exception($msg));

					}

					return new $this->map($data);

				}

				//execute(Array("class"=>"some\\class","merge"=>Array(.....
				///////////////////////////////////////////////////////////////

				if(is_array($this->map)){

					if(isset($this->map["class"])){

						if(!class_exists($this->map["class"])){
							$msg	=	"Class \"$this->map\" doesn't exists. Maybe you forgot to ".
										"require/include the class?";
						}

						if(array_key_exists("merge",$this->map)){

							if(!is_array($this->map["merge"])){

								throw(new \Exception("\"merge\" argument expected to be an array ".gettype($this->map["merge"])." given"));

							}

							foreach($this->map["merge"] as $key=>$val){

								$data[$key]=$val;

							}

						}

						return new $this->map["class"]($data);

					}

				}

			}

			public function rewind(){

				$this->position	=	0;
				$this->result->data_seek(0);
				$this->fetch();

			}

			public function current(){

				return $this->currentRow;

			}

			public function key(){

				return $this->position;

			}

			public function fetch(){

				$this->currentRow	=	$this->result->fetch_assoc();

				if(!$this->currentRow){
				
					return;

				}

				$result	=	new Row($this->currentRow,$this->select->getTable());

				return $this->currentRow=$this->map($result);

			}

			public function next(){

				$this->fetch();
				++$this->position;

			}

			public function getNumRows(){

				return $this->result->num_rows;

			}

			public function valid(){

				return $this->position < $this->result->num_rows;

			}

			public function offsetGet($offset){

				if(is_null($this->currentRow)){
					$this->fetch();
				}

				return $this->currentRow[$offset];

			}


			public function offsetSet($offset,$value){

				if(is_null($this->currentRow)){

					$this->fetch();

				}

				return $this->currentRow[$offset]	=	$value;

			}

			public function offsetUnset($offset){

				if(is_null($this->currentRow)){

					$this->fetch();

				}

				unset($this->currentRow[$offset]);

			}

			public function offsetExists($offset){

				if(is_null($this->currentRow)){

					$this->fetch();

				}

				return array_key_exists($offset,$this->currentRow);
				
			}

			public function __get($var){

				return $this->offsetGet($var);

			}

			//Also use with extreme care
			public function toJSON(Callable $transform=NULL){

				$data	=	Array();

				foreach($this->toArray() as $d){

					if(is_object($d)){

						$data[]	=	is_null($transform) ? sprintf('%s',$d) : $transform($d);
						continue;

					}

					$data[]	=	$d;

				}

				return json_encode($data);

			}

			public function walk(Callable $fn=NULL,$fnParams=NULL){

				if($this->getNumRows()==0){

					return;

				}

				foreach($this as $value){

					$fn($value,$fnParams);

				}

			}

			//Use with extreme care
			public function toArray(Callable $fn=NULL,$fnParams=NULL){

				if($this->getNumRows()==0){

					return Array();

				}

				$data	=	Array();

				foreach($this as $value){

					$data[]	=	$fn ? $fn($value,$fnParams) : $value;

				}

				return $data;

			}

			public function toExcel($fields=Array(),Callable $transform=NULL){

				if(!class_exists('\PHPExcel')){

					$msg	=	"For exporting a result set to excel you must download the PHPExcel";
					$msg	=	sprintf('%s package. Get it at https://phpexcel.codeplex.com');

					throw new \Exception($msg);

				}

				$fieldSize		=	sizeof($fields);

				$excel			=	new \PHPExcel();
				$info				=	sprintf('Apollo Framework %s',\apf\core\Kernel::VERSION);

				$excel->getProperties()->setCreator($info)
				->setLastModifiedBy($info);

				$excel->setActiveSheetIndex(0);

				$resultKeys	=	Array();

				foreach($this->result as $rowNumber=>$contents){

					foreach($contents as $key=>$rowContent){

						$resultKeys[]	=	$key;

					}

					break;

				}

				if($fieldSize){

					foreach($fields as $key=>$value){

						$heading[]		=	$value;
						$fields[$key]	=	is_numeric($key)	?	$value	:	$key;

					}

				}else{

					$heading	=	$resultKeys;

				}

				$excel->getActiveSheet()->fromArray($heading,'','A1');
				$excel->getActiveSheet()->getStyle('1:1')->getFont()->setBold(TRUE);

				$highest	=	$excel->getActiveSheet()->getHighestDataColumn();
				$highest	=	\PHPExcel_Cell::columnIndexFromString($highest);

				$count =	0;

				foreach($this as $rowNumber=>$contents){

					if($fieldSize){

						$tmpContent	=	Array();

						foreach($fields as $key=>$field){

							$tmpContent[$field]	=	!is_null($transform) ? $transform($key,$contents) : sprintf('%s',$contents[$field]);

						}

						$contents = $tmpContent;

					}

					foreach($contents as $key=>$rowContent){


						$columnLetter	=	\PHPExcel_Cell::stringFromColumnIndex($count++);
						$column			= 	sprintf('%s%d',$columnLetter,$rowNumber+2);

						$excel->getActiveSheet()->setCellValue($column,sprintf('%s',$rowContent));
						$excel->getActiveSheet()->getColumnDimension($columnLetter)->setAutoSize(TRUE);

					}

					$count=0;

				}

				$writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

				return $writer;

			}

			//Use with EXTREME care

			public function toChunkedArray($chunkAmount=NULL){

				$chunkAmount	=	(int)$chunkAmount;

				if($chunkAmount<=0){

					throw new \InvalidaArgumentException("Chunk amount must be greater than 0");

				}

				if($this->getNumRows()<$chunkAmount){

					return $this->toArray();

				}

				return array_chunk($this->toArray(),$chunkAmount);

			}

			public function __set($var,$value){

				return $this->offsetSet($var,$value);

			}

			public function __destruct(){

				$this->result->free();

			}
			
			public function __toString(){

				return(sprintf("%s",$this->currentRow));

			}

		}

	}

?>

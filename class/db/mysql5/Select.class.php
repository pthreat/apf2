<?php

	/**
	*MySQL query Builder class, this class is used to build SELECT SQL queries
	*/

	namespace apf\db\mysql5{

		class Select extends Query implements \ArrayAccess{

			private	$calcFoundRows	=	FALSE;

			public function __construct($table=NULL,$params=NULL){

				parent::__construct($table,$params);

			}

			public function __get($var){

				return $this->offsetGet($var);

			}

			public function __set($var,$value){

				return $this->offsetSet($var,$value);

			}

			public function getCalcFoundRows(){
				return $this->calcFoundRows;
			}

			public function getFoundRows(){

				if(!$this->getCalcFoundRows()){
					throw(new \Exception("To use this feature you must turn on the calcFoundRows")); 
				}

				$select	=	__CLASS__;
				$select	=	new $select(NULL,$this->params);
				$select->fields(Array("FOUND_ROWS()"=>"total"));
				$result	=	$select->execute();
				if(!is_null($result)){
					return $result["total"];
				}

			}

			public function calcFoundRows($boolean=TRUE){

				$this->calcFoundRows	=	(bool)$boolean;

			}

			public function offsetGet($offset){

				$this->validateResult();
				return $this->result[$offset];

			}

			private function validateResult(){

				if(is_null($this->result)){

					$this->execute();

					if(is_null($this->result)){

						throw(new \Exception("Query returned no rows"));

					}

				}

			}

			public function offsetSet($offset,$value){

				$this->validateResult();

				$this->result[$offset]	=	$value;

			}

			public function offsetUnset($offset){

				$this->validateResult();
				unset($this->result[$offset]);

			}

			public function offsetExists($offset){

				$this->validateResult();
				return isset($this->result[$offset]);
				
			}

			//This is just an accesory method for you being able to wrap a certain value

			public function group(Array $group){

				$this->sqlArray["group"] = $group;

			}

			public function toOutFile($file){

				$this->sqlArray["outfile"]	=	$file;

			}

			public function fields(Array $fields=Array(),$aliasOrder="LTR"){

				if($aliasOrder!=="LTR"&&$aliasOrder!=="RTL"){

					throw(new \Exception("Unknown alias order \"$aliasOrder\""));

				}

				$tmpFields	=	Array();

				foreach($fields as $k=>&$v){

					$k	=	$this->adapter->real_escape_string($k);
					$v	=	$this->adapter->real_escape_string($v);

					if(empty($v)){
						$v="''";
					}

					if(!is_numeric($k)){

						if($aliasOrder=="LTR"){
							$v	=	$k.' AS '. $v;
							continue;
						}

						$v	=	$v.' AS '. $k;

					}

				}

				$this->sqlArray["fields"]	=	$fields;

				return $this;

			}

			public function join(Join $join){

				$this->sqlArray["join"][]	=	$join;

			}

			public function offset($offset){

				\apf\validate\Int::mustBePositive($offset,"Offset must be a positive number");

				if((int)$offset){
					$this->sqlArray["offset"]	=	(int)$offset;
				}

				return $this;
				
			}

			public function getOffset(){

				return $this->sqlArray["offset"];

			}

			public function orderBy(Array $fields){

				foreach($fields as $field=>$sort){

					$sort	=	strtoupper($sort);

					if($sort!=="ASC"&&$sort!=="DESC"){

						throw(new \Exception("Unknown sorting order specified \"$sort\""));

					}

					$this->sqlArray["order"][$field]=$sort;

				}

				return;

			}

			public function getOrder(){

				if(!sizeof($this->sqlArray["order"])){

					return '';

				}

				$sql	=	"ORDER BY ";

				$order	=	Array();

				foreach($this->sqlArray["order"] as $field=>$sort){

					$field	=	$this->adapter->real_escape_string($field);
					$order[]	=	"$field $sort";

				}

				return $sql.implode(',',$order);

			}

			public function union(Select $select){

				$this->sqlArray["union"]	=	$select;

			}

			public function limit($limit){

				\apf\validate\Int::mustBePositive($limit,"Limit must be a positive number");

				$this->sqlArray["limit"]	=	(int)$limit;

				return $this;

			}

			public function getSQL(){

				$s			=	$this->space;

				$where	=	$this->sqlArray["where"];

				if(sizeof($this->sqlArray["fields"])){

					$fields	=	implode($this->fieldDelimiter,$this->sqlArray["fields"]);

					if($this->calcFoundRows){

						$fields	=	"SQL_CALC_FOUND_ROWS".$s.$fields;

					}

				}else{

					$fields		=	$this->table->getFields();

					$tmpFields	=	Array();

					foreach($fields as $f){

						$tmpFields[]	=	$f["name"];	

					}

					$fields	=	implode($this->fieldDelimiter,$tmpFields);

					if($this->calcFoundRows){

						$fields	.=	"SQL_CALC_FOUND_ROWS ".$s.$fields;

					}

					unset($tmpFields);

				}

				if(!is_null($this->sqlArray["union"])){

					$union	=	$this->sqlArray["union"]->getSQL();

				}else{

					$union	=	"";

				}

				$limit	=	"";

				if($this->sqlArray["limit"]){

					$limit	=	"LIMIT".$s.$this->sqlArray["limit"];

				}

				$from		=	NULL;

				if(!is_null($this->table)){
					$from	=	"FROM".$s.sprintf("%s",$this->table);
				}


				if(!is_null($this->sqlArray["where"])){

					$where	=	$s."WHERE".$s.$this->sqlArray["where"];

				}else{

					$where	=	"";

				}

				if(sizeof($this->sqlArray["group"])){

					$group	=	"GROUP BY ".implode($this->sqlArray["group"],',');

				}else{

					$group	=	'';

				}

				if(!is_null($this->sqlArray["offset"])){

					$offset	=	"OFFSET ".$this->sqlArray["offset"];

				}else{

					$offset	=	'';

				}

				$join	=	'';

				if(sizeof($this->sqlArray["join"])){

					foreach($this->sqlArray["join"] as $objJoin){

						$join .=	$objJoin->getSQL().$s;

					}

				}

				$having	=	'';
				$order	=	$this->getOrder();

				$sql	=	"SELECT".$s.$fields.$s.$from.$s.$join.$s.$where.$s.$group.$s.$having.$s.$order.$s.$limit.$s.$offset.$s.$union;
				return $sql;


			}

			public function getResult($map=NULL,$smart=TRUE){

				if(!$this->result->num_rows){

					return Array();

				}

				if($this->result->num_rows==1&&$smart){

					$result	=	new \apf\db\mysql5\select\Result($this,$map);
					$this->result	=	$result;
					return $this->result->fetch();

				}

				$this->result	=	new \apf\db\mysql5\select\Result($this,$map);

				return $this->result;

			}

			public function __call($method,$args){

				if(is_null($this->result)){
					throw(new \Exception("No valid result set found"));
				}

				call_user_func(Array($this->result,$method),$args);

			}

		}

	}
?>

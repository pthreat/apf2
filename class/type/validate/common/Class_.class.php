<?php 

	namespace apf\type\validate\common{

		use apf\type\validate\base\Str	as	ValidateString;

		class Class_{

			/**
			*Checks if a class exists.
			*@param String $name Class name
			*@return Int -1 class name is empty
			*@return boolean TRUE Class exists
			*@return boolean FALSE Class doesn't exists
			*/

			public static function exists($name){

				return class_exists($name);

			}

			/**
			*Checks if a class exists, imperative mode.
			*If the class doesn't exists it will throw an Exception
			*@param String $name class name
			*@param String $msg Exception message if any
			*@param Int $exCode Exception code
			*@throws \apf\exception\Validate if the class doesn't exists
			*/

			public static function mustExist($name,$msg=NULL,$exCode=0){

				if(!class_exists($name)){

					throw new \Exception("Class $name doesn't exists");

				}

				return TRUE;

			}

			/**
			*Checks if a class has a method named $method (boolean mode)
			*
			*@param String $name class name
			*@param String $method method to be checked 
			*@param Mixed  $filters, a set of filters to check for class attributes such as public,
			*					private, abstract, final, etc. You can specify a string such as "abstract"
			*					or you can specify a set of filters in an Array like structure
			*					for instance, specifying $filters to  Array('static','public') 
			*					would check for a method named $method that is both static AND public.
			*					Note: This is affected according to the mode you specify, 
			*					which is by default "all", meaning that every filter you specify
			*					must be present in the given method.
			*
			*@param String	$mode mode can be any of "all", "any" or "some". The default mode is "all"
			*
			*					"all" mode: the method must contain ALL and *ONLY* the parameters that you 
			*					specify in the $filters argument. If it contains extra attributes it will be
			*					considered as invalid.
			*
			*					"any" mode: the method must contain at least ONE of the parameters specified
			*					in $filters. Extra attributes don't matter.
			*
			*					"some" mode: the method must contain all of the attributes specified in
			*					$filters, but if it contains any other attribute it's still considered 
			*					valid.
			*					
			*					is necessary to exist in the given method for it to be TRUE.
			*					
			*					
			*@param String $msg Exception message if any
			*@param Int $exCode Exception code
			*
			*@return Int -6 if $filters are not null and an empty string was entered
			*@return Int -7 if $filters are an array but the array elements are not strings or it's empty
			*@return Int -8 ONLY if specified filters are empty
			*@return Int -9 ONLY if an invalid filter was specified
			*@return Int -10 Method doesn't exists
			*@return boolean TRUE if the class has a method named $method
			*@return boolean FALSE if the class doesn't has a method named $method
			*
			*@see self::parameterValidation for other return values
			*/

			public static function hasMethod($name,$method,$filters=NULL,$mode="all"){

				$validModes	=	Array('all','any','some');
				$stdValidation	=	self::parameterValidation($name,$method);

				//something went wrong
				if(!($stdValidation===TRUE)){

					return $stdValidation;

				}
				
				$rc	=	new \ReflectionClass($name);

				//If no filters where specified, then check if the given class has a given method
				//and thats it! We don't need further checking if the user hasn't specified any filters.
				if(is_null($filters)){

					return $rc->hasMethod($method);

				}
				
				//Check if specified $filters are invalid

				if(is_string($filters)){

					if(ValidateString::isEmpty($filters)){

						return -6;

					}

				}

				//If it's an array but the array is not made of strings
				if(!is_null(Vector::isArray($filters))&&!Vector::isMadeOfStrings($filters)){

					return -7;

				}
				
				if(is_string($filters)){

					$filters	=	Array($filters);

				}

				$mode	=	strtolower($mode);

				//Specified $mode is invalid (not one of all,any or some)
				if(ValidateString::isEmpty($mode)||!in_array($mode,$validModes)){

					return -7;

				}

				//Check if entered filters are not empty

				if(empty($filters)){

					return -8;

				}

				//Check if entered filters are valid

				foreach($filters as $filter){

					$filter	=	sprintf('IS_%s',strtoupper($filter));

					//If an invalid filter is detected, then return -9

					if(!self::hasConstant('\ReflectionMethod',$filter)){

						return -9;

					}

				}

				//Fetch all class methods
				$methods	=	$rc->getMethods();

				$found	=	FALSE;

				foreach($methods as $m){

					//If the current method is not equal to the selected one then continue
					//with the next element in the array

					if($m->name!==$method){

						continue;

					}

					$found	=	TRUE;
					break;

				}

				//Method not found

				if(!$found){

					return -8;

				}

				//Get all method modifiers (public, abstract, private, final, etc)
				$modifiers	=	\Reflection::getModifierNames($m->getModifiers());

				switch($mode){

					case "any":

						foreach($filters as $f){

							if(in_array($f,$modifiers)){

								return TRUE;

							}

						}

						return FALSE;

					break;

					case "some":

						$count			=	0;

						foreach($modifiers as $mod){

							if(in_array($mod,$filters)){

								$count++;

							}

						}

						return $count==sizeof($filters);

					break;

					case "all":

						foreach($modifiers as $mod){

							if(!in_array($mod,$filters)){

								return FALSE;

							}

						}

						return TRUE;

					break;

				}

			}

			/**
			*Checks if a class has a method named $method (imperative mode).
			*
			*@param String $name class name
			*@param String $method method to be checked 
			*@param Mixed  $filters, a set of filters to check for class attributes such as public,
			*					private, abstract, final, etc. You can specify a string such as "abstract"
			*					or you can specify a set of filters in an Array like structure
			*					for instance, specifying $filters to  Array('static','public') 
			*					would check for a method named $method that is both static AND public.
			*					Note: This is affected according to the mode you specify, 
			*					which is by default "all", meaning that every filter you specify
			*					must be present in the given method.
			*
			*@param String	$mode mode can be any of "all", "any" or "some". The default mode is "all"
			*
			*					"all" mode: the method must contain ALL and *ONLY* the parameters that you 
			*					specify in the $filters argument. If it contains extra attributes it will be
			*					considered as invalid.
			*
			*					"any" mode: the method must contain at least ONE of the parameters specified
			*					in $filters. Extra attributes don't matter.
			*
			*					"some" mode: the method must contain all of the attributes specified in
			*					$filters, but if it contains any other attribute it's still considered 
			*					valid.
			*					
			*					is necessary to exist in the given method for it to be TRUE.
			*					
			*					
			*@param String $msg Exception message if any
			*@param Int $exCode Exception code
			*
			*@throws \apf\exception\Validate with code -5 if the mode specifed is not one of 
			*			"all", "any" or "some".
			*
			*@throws \apf\exception\Validate with code -6 ONLY if specified filters are empty
			*@throws \apf\exception\Validate with code -7 if an invalid attribute was specified in the
			*			$filters argument.
			*@throws \apf\exception\Validate with code -8 if the method doesn't exists be aware that
			*			the existence of the method depends on the filters that you have specified
			*			(if any).
			*@throws \apf\exception\Validate with code $exCode if method doesn't exists
			*
			*@see parent::imperativeValidation for other exceptions
			*
			*/

			public static function mustHaveMethod($name,$method,$filters=NULL,$mode="all",$msg=NULL,$exCode=0){

				$hasMethod	=	self::hasMethod($name,$method,$filters,$mode);

				//Method has been found
				if($hasMethod===TRUE){

					return;

				}

				$exValues	=	Array(
										Array(
												"value"		=>	-6,
												"msg"		=>	'Specified mode is not one of "any", "all" or "some"'
										),
										Array(
												"value"		=>	-7,
												"msg"		=>	'Specified filters can not be empty'
										),
										Array(
												"value"		=>	-8,
												"msg"		=>	'Invalid filter attribute/s specified'
										),
										Array(
												"value"		=>	-9,
												"msg"		=>	"Method $method doesn't exists"
										),
										Array(
												"value"		=>	FALSE,
												"msg"		=>	"Method $method doesn't exists"
										)
				);

				parent::imperativeValidation($hasMethod,$exCode,$msg,$exValues);

			}

			/**
			*Checks if a class has a public method named $method.
			*This method is just an alias of self::hasMethod with pre specified parameters
			*@param String $name class name
			*@param String $method method to be checked 
			*@see self::hasMethod
			*/

			public static function hasPublicMethod($name,$method){

				return self::hasMethod($name,$method,"public","any");

			}

			/**
			*Checks if a class has a private method named $method.
			*This method is just an alias of self::hasMethod with pre specified parameters
			*@param String $name class name
			*@param String $method method to be checked 
			*@see self::hasMethod
			*/

			public static function hasPrivateMethod($name,$method){

				return self::hasMethod($name,$method,"private","any");

			}

			/**
			*Checks if a class has a public method named $method (imperative mode).
			*This method is just an alias of self::mustHaveMethod with pre specified parameters
			*@see self::mustHaveMethod
			*/

			public static function mustHavePublicMethod($name,$method,$msg=NULL,$exCode=0){

				return self::mustHaveMethod($name,$method,"public","any",$msg,$exCode);

			}

			/**
			*Checks if a class has a constant named $constant
			*@param String $name class name
			*@param String $constant constant to be checked 
			*@return boolean TRUE if the class has a constant named $constant
			*@return boolean FALSE if the class doesn't has a constant named $constant
			*@see self::parameterValidation for other return values
			*/

			public static function hasConstant($name,$constant){

				$stdValidation	=	self::parameterValidation($name,$constant);
				$rc				=	new \ReflectionClass($name);

				return $rc->hasConstant($constant);

			}

			/**
			*Checks if a class has a constant named $constant (imperative mode)
			*@param String $name class name
			*@param String $constant constant to be checked 
			*@throws \apf\exception\Validate with code -1 if the class name is empty
			*@throws \apf\exception\Validate with code -2 if the class doesn't exists
			*@throws \apf\exception\Validate with code $exCode if constant $constant doesn't exists
			*/

			public static function mustHaveConstant($name,$constant,$msg=NULL,$exCode=0){

				$hasConstant	=	self::hasConstant($name,$constant);

				if($hasConstant===TRUE){
					return;
				}

				$exValues	=	Array(
											Array(
													"value"	=>	FALSE,
													"msg"		=>	"Class $name doesn't has a constant named $constant"
											)
				);

				parent::imperativeValidation($hasConstant,$exCode,$msg,$exValues);

			}

			/**
			*Checks if a class has a parent named $parent
			*@param String $name Class name
			*@param String $parent Parent class name that the class should extend to
			*@return Int order where the parent has been found
			*@return boolean FALSE Asked parent class is not a parent of the given class $name
			*/

			public static function hasParent($name,$parent){

				$stdValidation	=	self::parameterValidation($name,$parent);

				if(!class_exists($parent)){

					return -4;

				}

				if(!($stdValidation===TRUE)){

					return $stdValidation;

				}

				$rc				=	new \ReflectionClass($name);
				$parent			=	strtolower($parent);
				$parentCount	=	0;

				while($p=$rc->getParentClass()){

					$parentCount++;

					$currentParent	=	strtolower($p->getName());
					$rc				=	$p;

					if($currentParent==$parent){

						return $parentCount;

					}

				}

				return FALSE;

			}

			/**
			*Checks if a class $name has a parent named $parent IN A GIVEN ORDER.
			*By given order, I mean, you can check if the class specified extends immediately
			*to the $parent class ($order=1) or if the inheritance goes one level above
			*($order=2)
			*<code>
			*#Example
			*class a {
			*}
			*
			*class b extends a{
			*}
			*
			*class c extends b{
			*}
			*
			*$doesClassCExtendsClassB	=	\apf\validate\Class_::hasParentInOrder("c","b",1);
			*
			*var_dump($doesClassCExtendsClassB); #Would say TRUE
			*
			*$doesClassBExtendsClassA	=	\apf\validate\Class_::hasParentInOrder("b","a",1);
			*
			*var_dump($doesClassCExtendsClassB); #Would say TRUE
			*
			*#But ...
			*
			*$doesClassCExtendsClassA	=	\apf\validate\Class_::hasParentInOrder("c","a",1);
			*
			*var_dump($doesClassCExtendsClassB); #Would say FALSE! Since c immediately extends to b
			*#Note that the order in the previous example is 1 :)
			*
			*#If you would like to check for C extending to A using this method
			*#Note the order being set to 2.
			*
			*$doesClassCExtendsClassA	=	\apf\validate\Class_::hasParentInOrder("c","a",2);
			*
			*var_dump($doesClassCExtendsClassB); #Would say TRUE since c does extends a but in second 
			*order.
			*
			*</code>
			*
			*@return Int -4 In case the specified order is incorrect (not numeric or 0 or lower than 0)
			*@return bool TRUE if the class has a parent in the given order
			*@return bool FALSE if the class DOES NOT HAVE a parent in the given order
			*/

			public static function hasParentInOrder($name,$parent,$order){

				$hasParent	=	self::hasParent($name,$parent);

				$order	=	(int)$order;

				if($order<=0){

					return -4;

				}

				if($hasParent==$order){

					return TRUE;

				}

				return FALSE;

			}

			/**
			*Validates if a class has a parent named $parent (imperative mode)
			*@param String $name Class name
			*@param String $parent Parent class name
			*@throw \apf\validate\Exception if the parent hasn't been found
			*/

			public static function mustHaveParent($name,$parent,$msg=NULL,$exCode=0){

				$hasParent	=	self::hasParent($name,$parent);

				if($hasParent > 0){

					return;

				}

				$exValues	=	Array(
											Array(
													"value"	=>	-4,
													"msg"		=>	"Parent class \"$parent\" doesn't exists"
											),
											Array(
													"value"	=>	FALSE,
													"msg"		=>	"Class \"$name\" doesn't has a parent named \"$parent\""
											)
				);

				parent::imperativeValidation($hasParent,$exCode,$msg,$exValues);

			}

			/**
			*Validates if a class has a parent named $parent in a given position (imperative mode)
			*@see self::hasParentInOrder for further information.
			*/

			public static function mustHaveParentInOrder($name,$parent,$order=1,$msg=NULL,$exCode=0){

				$hasParentInOrder	=	self::hasParentInOrder($name,$parent,$order);

				if($hasParentInOrder===$order){

					return;

				}

				$exValues	=	Array(
											Array(
													"value"		=>	FALSE,
													"msg"			=>	"Class \"$name\" doesn't has a parent in order \"$order\"",
													"exception"	=>	'\apf\exception\Validate'
											),
											Array(
													"value"		=>	-4,
													"msg"			=>	"Invalid order argument specified \"$order\""
											)
				);

				parent::imperativeValidation($hasParentInOrder,$exCode,$msg,$exValues);

			}

			/**
			*Checks if a class has a property named $property
			*@param String $name class name
			*@param String $property property to be checked 
			*@return Int -4 ONLY If a filter was specified and such filter is invalid
			*@return boolean TRUE if the class has a property named $property
			*@return boolean FALSE if the class doesn't has a property named $property
			*@see self::parameterValidation for other return values.
			*/

			public static function hasProperty($name,$property,$filter=NULL){

				$stdValidation	=	self::parameterValidation($name,$property);

				if(!($stdValidation===TRUE)){

					return $stdValidation;

				}

				$rc	=	new \ReflectionClass($name);

				if(!is_null($filter)){

					$_filter		=	(int)$filter;

					$constant	=	sprintf('IS_%s',strtoupper($filter));

					if(!self::hasConstant('\ReflectionProperty',$constant)){

						return -4;

					}

					$_filter	=	constant(sprintf('\ReflectionProperty::%s',$constant));

					return (boolean)$rc->getProperties($_filter);

				}

				return $rc->hasProperty($property);

			}

			/**
			*Checks if a class has a property named $property (imperative mode)
			*@param String $name class name
			*@param String $property property to be checked 
			*@throws \apf\exception\Validate with code -1 if the class name is empty
			*@throws \apf\exception\Validate with code -2 if the class doesn't exists
			*@throws \apf\exception\Validate with code -3 if the property name is empty
			*@throws \apf\exception\Validate with code $exCode if the property doesn't exists
			*/

			public static function mustHaveProperty($name,$property,$filter=NULL,$msg=NULL,$exCode=0){

				$hasProperty	=	self::hasProperty($name,$property,$filter);

				if($hasProperty===TRUE){

					return;

				}

				$exValues	=	Array(
											Array(
													"value"	=>	FALSE,
													"msg"		=>	"Class $name doesn't has a property named $property"
											)
				);

				parent::imperativeValidation($hasProperty,$exCode,$msg,$exValues);

			}

			/**
			*Checks if a class is final.
			*@return bool TRUE if the class is final
			*@return bool FALSE if the class is NOT final
			*@see self::parameterValidation for other return codes.
			*/

			public static function isFinal($class){

				$stdValidation	=	self::parameterValidation($class,'TRUE');

				if(!($stdValidation===TRUE)){

					return $stdValidation;

				}

				$rc	=	new \ReflectionClass($class);

				return $rc->isFinal();

			}

			/**
			*Checks if a class is final (imperative  mode).
			*@param String $class class name
			*@param String $msg Exception message (optional)
			*@param Int Exception code which must be greater than 0 (optional)
			*@throws \InvalidArgumentException if there's any problem within supplied arguments.
			*@throws \apf\exception\Validate if the class is not final.
			*@see parent::imperativeValidation for other exception codes
			*/

			public static function mustBeFinal($class,$msg=NULL,$exCode=0){

				$isFinal	=	self::isFinal($class,'TRUE');

				if($isFinal===TRUE){

					return;

				}

				$exValues	=	Array(
											Array(
													"value"		=>	FALSE,
													"msg"			=>	"Class $class is not final",
													"exception"	=>	'\apf\exception\Validate'
											)
				);

				parent::imperativeValidation($isFinal,$exCode,$msg,$exValues);

			}

			//Check if it's an apollo framework class

			public static function isAPF($name){

			}

			/**
			*Checks if a class is abstract.
			*@return bool TRUE if the class is abstract
			*@return bool FALSE if the class is NOT abstract
			*@see self::parameterValidation for other return codes.
			*/

			public static function isAbstract($class){

				$stdValidation	=	self::parameterValidation($class,'TRUE');

				if(!($stdValidation===TRUE)){

					return $stdValidation;

				}

				$rc	=	new \ReflectionClass($class);

				return $rc->isAbstract();

			}

			/**
			*Checks if a class is abstract (imperative  mode).
			*@param String $class class name
			*@param String $msg Exception message (optional)
			*@param Int Exception code which must be greater than 0 (optional)
			*@throws \InvalidArgumentException if there's any problem within supplied arguments.
			*@throws \apf\exception\Validate if the class is not abstract.
			*@see parent::imperativeValidation for other exception codes
			*/

			public static function mustBeAbstract($class,$msg=NULL,$exCode=0){

				$isAbstract	=	self::isAbstract($class,'TRUE');

				if($isAbstract===TRUE){

					return;

				}

				$exValues	=	Array(
											Array(
													"value"		=>	FALSE,
													"msg"			=>	"Class $class is not abstract",
													"exception"	=>	'\apf\exception\Validate'
											)
				);

				parent::imperativeValidation($isAbstract,$exCode,$msg,$exValues);

			}
			
			/**
			 *  Validates that a class $name is in a certain namespace $namespace
			 * @param String $name Class name
			 * @param String $namespace Namespace name
			 * @return boolean TRUE Class $class is in namespace $namespace
			 * @return boolean TRUE Class $class is NOT in namespace $namespace
			 * @see self::parameterValidation for other return values
			 */

			public static function isInNamespace($name,$namespace){

				$stdValidation	=	self::parameterValidation($name,$namespace);
				
				if(!($stdValidation===TRUE)){

					return $stdValidation;

				}
				
				$rc	=	new \ReflectionClass($name);
				return $rc->getNamespaceName()==$namespace;
				
			}

			/**
			 *  Validates that a class $name is in a certain namespace $namespace (imperative mode)
			 * @param String $name Class name
			 * @param String $namespace Namespace name
			 * @param String $msg Exception message if any
			 * @param Int Exception code (must be greater than 0)
			 * @throws \InvalidaArgumentException if any of the arguments are not correct
			 * @throws \apf\exception\Validate if the class is not in namespace $namespace
			 * @see parent::imperativeValidation for other return values
			 */
			
			public static function mustBeInNamespace($name,$namespace,$msg=NULL,$exCode=0){
		
				$isInNamespace	=	self::isInNamespace($name, $namespace);
				
				if($isInNamespace===TRUE){

					return;

				}
				
				$exValues	=	Array(
					
										Array(
												"value"		=>	FALSE,
												"msg"		=>	"Class $name is not in namespace $namespace",
												"exception"	=>	'\apf\exception\Validate'	
										)

				);
				
				imperativeValidation($isInNamespace,$exCode,$msg,$exValues);
				
			}

		}

	}

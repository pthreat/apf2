<?php

	/**
	*This class is part of Apollo PHP Framework.
	*
	*Namespace	:	\apf\validate
	*Class		:	Email
	*Description:	Validates email addresses
	*
	*
	*Author		:	Federico Stange <jpfstange@gmail.com>
	*License		:	3 clause BSD
	*
	*Copyright (c) 2015, Federico Stange
	*
	*All rights reserved.
	*
	*Redistribution and use in source and binary forms, with or without modification, 
	*are permitted provided that the following conditions are met:
	*
	*1. Redistributions of source code must retain the above copyright notice, 
	*this list of conditions and the following disclaimer.
	*
	*2. Redistributions in binary form must reproduce the above copyright notice, 
	*this list of conditions and the following disclaimer in the documentation and/or other 
	*materials provided with the distribution.
	*
	*3. Neither the name of the copyright holder nor the names of its contributors may be used to 
	*endorse or promote products derived from this software without specific prior written permission.
	*
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS 
	*OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY 
	*AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER 
	*OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
	*CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	*LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY 
	*OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
	*ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY 
	*OF SUCH DAMAGE.
	*
	*/

	namespace apf\validate{

		class Email extends Base{


			public static function isEmail($val){

				return self::parameterValidation($val);

			}

			public static function mustBeEmail($val,$msg=NULL,$exCode=0){

				$stdVal	=	self::parameterValidation($val);

				if($stdVal===TRUE){

					return TRUE;

				}

				parent::imperativeValidation($stdVal,$exCode,$msg);

			}
			
			/**
			 * Private method which normalizes email argument validation throughout all of the 
			 * contained methods of this class.
			 * @param String $email
			 * @return Int -1 Given argument is not a string
			 * @return Int -2 Given argument is empty
			 * @return boolean TRUE argument is a valid email
			 * @return boolean FALSE $email argument is an invalid email
			 */

			public static function parameterValidation($email){
				
				if(!String::isString($email)){	

					return -1;

				}

				if(String::isEmpty($email,$useTrim=TRUE)){

					return -2;

				}

				
				$addressDomain	=	substr($email,strpos($email,'@')+1);
				$mxRecords		=	Array();

				if(!getmxrr($addressDomain,$mxRecords)){

					return -3;

				}

				return filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE	?	-4	:	TRUE;

			}
			
			public static function getStandardExceptionMessages(){

				return Array(
							Array(
									"value"	=>	-1,
									"msg"	=>	"Email parameter must be a string"
							),
							Array(
									"value"	=>	-2,
									"msg"	=>	"Email parameter can not be empty"
							),
							Array(
									"value"	=>	-3,
									"msg"	=>	"Email domain is invalid"
							),
							Array(
									"value"	=>	-4,
									"msg"	=>	"Invalid email address"
							)

				);

			}

			/**
			 * Validates that a given email address $email is valid or not
			 * @param String $email
			 * @return Int -1 if the provided email is not a string
			 * @return Int -2 If the provided email is empty
			 * @return boolean FALSE if the provided email is not an email
			 * @return boolean TRUE if the provided email is an email
			 */
			
			public static function isAddress($email){
				
				return self::parameterValidation($email);
	
			}
			
			/**
			 * Validates that a given email address $email is valid (imperative mode)
			 * @param String $email
			 * @throws \InvalidArgumentException if any of the arguments are incorrect
			 * @throws \apf\exception\Validate if the given email is not an email address
			 */
			
			public static function mustBeAddress($email,$msg=NULL,$exCode=0){
				
				$isAddress	=	self::isAddress($email);
				
				if($isAddress===TRUE){

					return;

				}
				
				parent::imperativeValidation($isAddress, $exCode, $msg);

			}

			/**
			 * Validates that an email address belongs to  a certain domain.
			 * For instance, given the email jpfstange@gmail.com
			 *And having the following example:
			 * <code>
			 * $email	=	"jpfstange@gmail.com";
			 * $isGmail	=	\apf\validate\Email::domain($email,"gmail.com");
			 * var_dump($isGmail); #Would say TRUE since jpfstange@gmail.com address belongs to domain 
			 * gmail.com
			 * </code>
			 * @param String $address
			 * @param String $domain
			 * @param type $msg
			 * @param type $exCode
			 * @return type
			 * @throws \apf\exception\Validate
			 */
			public static function hasDomain($address,$domain,$msg=NULL,$exCode=0){

				$stdValidation	=	self::parameterValidation($email,$domain);
				
				if(!($stdValidation===TRUE)){
					return $stdValidation;
				}

				$addressDomain	=	substr($address,strpos($address,'@')+1);
				$addressDomain	=	substr($addressDomain,strpos($addressDomain,'.'));

				if($addressDomain!==$domain){

					$msg	=	empty($msg)	?	"Domain $addressDomain doesn't matches with domain $domain" : $msg;
					throw new \apf\exception\Validate($msg);

				}

				return $domain;

			}

			public static function domains($address,Array $domains,$msg=NULL,$exCode=0){

				self::address($address,$msg,-2);

				$domainsMsg	=	empty($msg)	?	"You must especify a non empty list of domains to match the email address domain against"	:	$msg;

				Array_::mustBeNotEmpty($domains,$msg,-3);

				$addressDomain	=	substr($address,strpos($address,'@')+1);

				foreach($domains as $domain){

					if(strtolower($addressDomain) == strtolower($domain)){

						return TRUE;

					}

				}

				$msg	=	empty($msg)	?	sprintf('Email domain doesn\'t matches with any of the domains listed: %s',implode(',',$domains)) : $msg;

				throw new \apf\exception\Validate($msg,$exCode);

			}

		}

	}

<?php

	namespace apf\web\service\google{

		use apf\parser\Uri			as	Uri;
		use apf\http\Adapter			as HttpAdapter;
		use apf\validate\Str			as ValidateString;
		use apf\http\adapter\Ecurl;

		class Translate{

			private	$fromLanguage		=	NULL;
			private	$toLanguage			=	NULL;
			private	$text					=	NULL;
			private	$translatedText	=	NULL;
			private	$adapter				=	NULL;

			const		TRANSLATE_URL	=	'https://translate.google.com';

			public function __construct($fromLanguage='en',$destinationLanguage=NULL,$text=NULL){

				if(!is_null($fromLanguage)){

					$this->setFromLanguage($fromLanguage);

				}

				if(!is_null($destinationLanguage)){

					$this->setDestinationLanguage($destinationLanguage);

				}

				if(!is_null($text)){

					$this->setText($text);

				}

			}

			public function setText($text){
				
				$this->text	=	ValidateString::mustBeNotEmpty($text,$trim=TRUE,'Text to translate must be not empty');

				return $this;

			}

			public function getText(){

				return $this->text;

			}

			public function setDestinationLanguage($destinationLanguage){

				$msg	=	'Destination language must be 2 characters wide';
				ValidateString::mustHaveLengthEqualTo($destinationLanguage,2,$trim=TRUE);
				$this->destinationLanguage	=	trim($destinationLanguage);

				return $this;

			}

			public function getDestinationLanguage(){

				return $this->destinationLanguage;

			}

			public function setFromLanguage($fromLanguage){

				$msg	=	'From language must be 2 characters wide';
				ValidateString::mustHaveLengthEqualTo($fromLanguage,2,$trim=TRUE);
				$this->fromLanguage	=	trim($fromLanguage);

				return $fromLanguage;

			}

			public function getFromLanguage(){

				return $this->fromLanguage;

			}

			public function setAdapter(HttpAdapter $adapter){

				$this->adapter	=	$adapter;
				return $this;

			}

			public function getAdapter(){

				return $this->adapter;

			}

			private function buildDefaultAdapter(){

				$uri		=	new Uri(self::TRANSLATE_URL);
				$uri->addRequestVariable('sl',$this->fromLanguage);
				$uri->addRequestVariable('tl',$this->destinationLanguage);
				$uri->addRequestVariable('text',$this->text);

				$adapter	=	new Ecurl($uri);
				$adapter->setHttpMethod('GET');

				return $adapter;

			}

			private function parseResult($result){

				$cut		=	'TRANSLATED_TEXT=';
				$result	=	substr($result,strpos($result,$cut)+strlen($cut));
				return trim(substr($result,0,strpos($result,';')),"'");

			}

			private function __translate(){

				try{

					return $this->translate();

				}catch(\Exception $e){

					return $e->getMessage();

				}

			}

			public function translate(){

				$this->setDestinationLanguage($this->destinationLanguage);
				$this->setFromLanguage($this->fromLanguage);
				$this->setText($this->text);

				$adapter	=	is_null($this->adapter)	?	$this->buildDefaultAdapter()	:	$this->adapter;

				$this->translatedText	=	$this->parseResult($adapter->connect());

				return $this->translatedText;

			}

			public function getTranslatedText(){

				return $this->translatedText;

			}

			public function __toString(){

				return sprintf('%s',$this->__translate());

			}

		}

	}


<?php

    namespace apf\web\rss{

        class Item{
            
            protected $attr   =   Array();

            public function __set($var,$value){
                $this->attr[$var]   =   $value;
            }
            
            public function __get($var){
                return $this->attr[$var];
            }
            
            public function __toString(){

                return $this->title;

            }
            
        }
        
    }


?>

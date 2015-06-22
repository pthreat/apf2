<?php

    namespace apf\web\rss{

        class HtmlItem extends Item{

            public function __toString(){
                return "<a href=\"".$this->link."\">".$this->title."</a>";
            }            

        }

    }

?>

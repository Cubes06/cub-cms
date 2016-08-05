<?php

    class Application_Model_Filter_UrlSlug implements Zend_Filter_Interface {
        
        
        public function filter($value) {
            
            //zemeni sve sto nije navedeno (^); u-unicode
            $value = preg_replace('/[^\p{L}\p{N}]/u', '-', $value); // oznaka za sve brojeve \p{N};     oznaka za sva slova \p{L};
            $value = preg_replace('/(\s+)/', '-', $value);
            $value = preg_replace('/(\-+)/', '-', $value);
            $value = trim($value, '-');
            return $value;
            
        }

    }
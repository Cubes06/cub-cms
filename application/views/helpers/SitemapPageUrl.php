<?php

    class Zend_View_Helper_SitemapPageUrl extends Zend_View_Helper_Abstract {
        
        public function sitemapPageUrl($id) {
            
            $sitemapPagesMap = Application_Model_DbTable_CmsSitemapPages::getSitemapPagesMap();
            
            if (isset($sitemapPagesMap[$id])) {
                return $this->view->baseUrl($sitemapPagesMap[$id]['url']);
            }
            else {
                return '';
            }
            
        }//endf
        

    }//end of class: Zend_View_Helper_MemberUrl


<?php

    class Admin_SitemapController extends Zend_Controller_Action {
        
        public function indexAction() {
                    
            $request = $this->getRequest();
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
            );
            
            //if no request_id parameter, than $parameterId will be 0
            $parentId = (int) $request->getParam('parent_id', 0); //default is 0
            
            if ($parentId < 0) {
                throw new Zend_Controller_Router_Exception('Invalid parent id for sitemap pages', 404);
            }
            
            $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
            
            $parentSitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($parentId);
            
            if (!$parentSitemapPage && $parentId != 0) {
                throw new Zend_Controller_Router_Exception('No parent is found for sitemap pages', 404);
            }
            
            
            $sitemapPages = $cmsSitemapPagesDbTable->search(array(
                'filters' => array(
                    'parent_id' => $parentId
                ),
                'orders' => array(
                    'order_number' => 'ASC'
                ),
                //'limit' => 50,
                //'page' => 1
            ));
            
            $sitemapPageBreadcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBreadcrumbs($parentId);
            
            $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
            $this->view->sitemapPages = $sitemapPages;
            $this->view->systemMessages = $systemMessages;
            
        }//endf
        
        
    } //end of class: Admin_SitemapController


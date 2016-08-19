<?php

    class IndexController extends Zend_Controller_Action {

        public function init() {
//            $cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
//            
//            $select = $cmsClientsDbTable->select();
//            $select->order('order_number ASC');
//                   
//            $clients = $cmsClientsDbTable->fetchAll($select);
//            
//            $this->view->clients = $clients;
//            $this->view->systemMessages = $systemMessages;
        }

        public function indexAction() {
            // ovde dovuci enable-ovane slajdove i proslediti ih
            $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexslides();

            $indexSlides = $cmsIndexSlidesDbTable->search(array(
                'filters' => array(
                    'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED
                ),
                'orders' => array(
                    'order_number' => 'ASC'
                ),
            ));
//            print_r($indexSlides);
//            die();
            $this->view->indexSlides = $indexSlides;        
            
            
   
            $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
//            $select = $cmsServicesDbTable->select();
//            $select->where('status = ?', Application_Model_DbTable_CmsServices::STATUS_ENABLED)
//                    ->order('order_number ASC')
//                    ->limit(4);
//            $services = $cmsServicesDbTable->fetchAll($select);
//            $this->view->services = $services;
            
            $topServices = $cmsServicesDbTable->search(array(
                'filters' => array(
                    'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED
                ),
                'orders' => array(
                    'order_number' => 'ASC'
                ),
                'limit' => 4
            ));
                    
            $this->view->services = $topServices;
            
            
            $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
            $servicesTopPage = $cmsSitemapPagesDbTable->search(array(
                'filters' => array(
                    'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                    'type' => 'ServicesPage',
                    'parent_id' => 0
                )
            ));
            $servicesTopPage = !empty($servicesTopPage) ? $servicesTopPage : null;
            $this->view->servicesPage = $servicesTopPage;
            
            
            
            
            
            $photoGalleriesSitemapPages = $cmsSitemapPagesDbTable->search(array(
                    'filters' => array(
                            'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                            'type' => 'PhotoGalleriesPage'
                    ),
                    'limit' => 1
            ));
            $photoGalleriesSitemapPage = !empty($photoGalleriesSitemapPages) ? $photoGalleriesSitemapPages[0] : null;

            $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
            $photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
                    'filters' => array(
                            'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED
                    ),
                    'orders' => array(
                            'order_number' => 'ASC'
                    ),
                    'limit' => 3
            ));

            
            $this->view->photoGalleriesSitemapPage = $photoGalleriesSitemapPage;
            $this->view->photoGalleries = $photoGalleries;
            
            
        }//endf
        
        
        public function testAction() {
            
            
        }//endf
    
        
        
    } // end of class: IndexController
    

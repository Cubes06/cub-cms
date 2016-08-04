<?php

    class Admin_DashboardController extends Zend_Controller_Action {
        
        public function indexAction(){
            
            Zend_Layout::getMvcInstance()->setLayout('admin');


            $flashMessenger = $this->getHelper('FlashMessenger');
            
            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $this->view->systemMessages = $systemMessages;
            
        }//endf
        
        
        public function dataAction() {
            
            //ove dve linije mozda ne trebaju ako to isto uradi: $this->getHelper('Json')->sendJson($niz);
//            Zend_Layout::getMvcInstance()->disableLayout();
//            $this->_helper->viewRenderer->setNoRender(true);
            
            //Members
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
            $activeMembers = $cmsMembersTable->getActiveMembers();
            $totalMembers = $cmsMembersTable->getTotalMembers();
            $activeMembers = $cmsMembersTable->count(array(
               'status' => Application_Model_DbTable_CmsMembers::STATUS_ENABLED
            ));
            $totalMembers = $cmsMembersTable->count();
            
            //Services
            $cmsServicesTable = new Application_Model_DbTable_CmsServices();
//            $activeServices = $cmsServicesTable->getActiveServices();
//            $totalServices = $cmsServicesTable->getTotalServices();
            $activeServices = $cmsServicesTable->count(array(
                'status' => Application_Model_DbTable_CmsServices::STATUS_ENABLED
            ));
            $totalServices = $cmsServicesTable->count();
            
            //Users
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
//            $activeUsers = $cmsUsersTable->getActiveUsers();
//            $totalUsers = $cmsUsersTable->getTotalUsers();
            $activeUsers = $cmsUsersTable->count(array(
                'status' => Application_Model_DbTable_CmsUsers::STATUS_ENABLED
            ));
            $totalUsers = $cmsUsersTable->count();
            
            //Clients
            $cmsClientsTable = new Application_Model_DbTable_CmsClients();
//            $activeClients = $cmsClientsTable->getActiveClients();
//            $totalClients = $cmsClientsTable->getTotalClients();
            $activeClients = $cmsClientsTable->count(array(
                'status' => Application_Model_DbTable_CmsClients::STATUS_ENABLED
            ));
            $totalClients = $cmsClientsTable->count();
            
            $niz = array(
                "activeMembers" => $activeMembers, 
                "totalMembers" => $totalMembers,
                "activeServices" => $activeServices,
                "totalServices" => $totalServices,
                "activeUsers" => $activeUsers,
                "totalUsers" => $totalUsers,
                "activeClients" => $activeClients,
                "totalClients" => $totalClients
            );
            
            $this->getHelper('Json')->sendJson($niz);
            
            
        }//endf
        
    } //end of class: Admin_DashboardController


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
            
            //Services
            $cmsServicesTable = new Application_Model_DbTable_CmsServices();
            $activeServices = $cmsServicesTable->getActiveServices();
            $totalServices = $cmsServicesTable->getTotalServices();
            
            //Users
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            $activeUsers = $cmsUsersTable->getActiveUsers();
            $totalUsers = $cmsUsersTable->getTotalUsers();
            
            //Clients
            $cmsClientsTable = new Application_Model_DbTable_CmsClients();
            $activeClients = $cmsClientsTable->getActiveClients();
            $totalClients = $cmsClientsTable->getTotalClients();
            
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


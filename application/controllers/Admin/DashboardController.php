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
        
    } //end of class: Admin_DashboardController


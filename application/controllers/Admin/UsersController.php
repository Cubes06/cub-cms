<?php

    class Admin_UsersController extends Zend_Controller_Action {

        
        public function indexAction() {

            $flashMessenger = $this->getHelper('FlashMessenger');

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
            );

            
            $this->view->users = array();
            $this->view->systemMessages = $systemMessages;
        }//endf
        

        public function addAction() {
            $request = $this->getRequest(); //podaci iz url-a iz forme sa koje dolazimo 
            $flashMessenger = $this->getHelper('FlashMessenger');  // za prenosenje sistemskih poruka

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_UserAdd();
            
            //default form data
            $form->populate(array(
                
            ));
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'save') {

                try {
                    //check form is valid
                    if (!$form->isValid($request->getPost())) {
                        throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new user');
                    }

                    //get form data
                    $formData = $form->getValues(); // dobijamo filtrirane i validirane podatke
                    
                    
                    $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                    
                    $userId = $cmsUsersTable->insertUser($formData);
                    

                    // do actual task
                    //save to database etc
                    
                    //set system message
                    $flashMessenger->addMessage('User has been saved.', 'success');

                    //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                } 
                catch (Application_Model_Exception_InvalidInput $ex) {
                    $systemMessages['errors'][] = $ex->getMessage();
                }
                
            }

            $this->view->systemMessages = $systemMessages;
            $this->view->form = $form;
        }//endf
        
        
        public function editAction() {
		
	    $request = $this->getRequest();
            
            $id = (int) $request->getParam('id'); //(int) pretvara slova u nule
            
            if ($id <= 0) {
                throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404); // ovako prekidamo izvrsavanje programa i prikazujemo 'page not found'
            }
            
            $loggedInUser = Zend_Auth::getInstance()->getIdentity();
            
            if ($id == $loggedInUser['id']) {
                //throw new Zend_Controller_Router_Exception('Go to edit profile page! ' . $id, 404);
                $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_profile',
                                'action' => 'edit'
                                ), 'default', true);
            }
            
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            $user = $cmsUsersTable->getUserById($id);
            
            if (empty($user)) {
                throw new Zend_Controller_Router_Exception('No user is found with id: ' . $id, 404);
            }
            
//            print_r($user);
//            die();
            
            $flashMessenger = $this->getHelper('FlashMessenger');  // za prenosenje sistemskih poruka

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_UserEdit($id);
            
            //default form data
            $form->populate($user); //$user je sam po sebi array
            
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'update') {

                try {
                    //check form is valid
                    if (!$form->isValid($request->getPost())) {
                        throw new Application_Model_Exception_InvalidInput('Invalid data was sent for user ');
                    }

                    //get form data
                    $formData = $form->getValues(); // dobijamo filtrirane i validirane podatke
                    
                    
                    //radimo update postojeceg zapisa u tabeli
                    $cmsUsersTable->updateUser($user['id'], $formData);
                    

                    // do actual task
                    //save to database etc
                    
                    //set system message
                    $flashMessenger->addMessage('User has been updated.', 'success');

                    //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                } 
                catch (Application_Model_Exception_InvalidInput $ex) {
                    $systemMessages['errors'][] = $ex->getMessage();
                }
                
            }

            $this->view->systemMessages = $systemMessages;
            $this->view->form = $form;
            
            $this->view->user = $user;
            
	}
        
        
        public function deleteAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'delete') {
                // request is not post or task is not delete
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id);
                }
                $loggedInUser = Zend_Auth::getInstance()->getIdentity();
                if ($id == $loggedInUser['id']) {
                    //redirect user to edit profile page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_profile',
                                'action' => 'edit'
                                    ), 'default', true);
                }
                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                $user = $cmsUsersTable->getUserById($id);

                if (empty($user)) {
                    throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id, 'errors');
                }
                
                $loggedInUser = Zend_Auth::getInstance()->getIdentity();
                

                $cmsUsersTable->deleteUser($id);
                
                //proveravamo da li je zahtev ajax ili ne
                $request instanceof Zend_Controller_Request_Http;
                
                if ($request->isXmlHttpRequest()) {
                    //request is ajax request
                    //send response as json
                    
                    $responseJson = array(
                        'status' => 'ok',
                        'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been deleted.', 'success'
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                }
                else {
                    $flashMessenger->addMessage('User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been deleted.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                }
                
                
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                if ($request->isXmlHttpRequest()) {
                    //request is ajax
                    $responseJson = array(
                        'status' => 'error',
                        'statusMessage' => $ex->getMessage()
                    );
                    //send json as response
                    $this->getHelper('Json')->sendJson($responseJson);
                }
                else {
                    $flashMessenger->addMessage($ex->getMessage(), 'errors');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                }
            }
            
        }
        
        
        public function disableAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'disable') {
                // request is not post or task is not disable
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id);
                }

               
                $loggedInUser = Zend_Auth::getInstance()->getIdentity();
                if ($id == $loggedInUser['id']) {
                    //redirect user to edit profile page
                    throw new Application_Model_Exception_InvalidInput('You can not disable your account!');
                }
                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                $user = $cmsUsersTable->getUserById($id);
                if (empty($user)) {
                    throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id);
                }
                $cmsUsersTable->disableUser($id);
                
                //proveravamo da li je zahtev ajax ili ne
                $request instanceof Zend_Controller_Request_Http;
                
                if ($request->isXmlHttpRequest()) {
                    //request is ajax request
                    //send response as json
                    
                    $responseJson = array(
                        'status' => 'ok',
                        'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been disabled.', 'success'
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                    
                }
                else {
                    // ako nije ajax onda uradi obican redirect
                    $flashMessenger->addMessage('User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been disabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                }

            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                if ($request->isXmlHttpRequest()) {
                    //request is ajax
                    $responseJson = array(
                        'status' => 'error',
                        'statusMessage' => $ex->getMessage()
                    );
                    //send json as response
                    $this->getHelper('Json')->sendJson($responseJson);
                }
                else {
                    //request is not ajax
                    $flashMessenger->addMessage($ex->getMessage(), 'errors');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                }
            }
            

            
        }
        
              
        public function enableAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'enable') {
                // request is not post or task is not enable
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id);
                }
                
                $loggedInUser = Zend_Auth::getInstance()->getIdentity();
                if ($id == $loggedInUser['id']) {
                    //redirect user to edit profile page
                    throw new Application_Model_Exception_InvalidInput('You can not enable your account!');
                }

                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                $user = $cmsUsersTable->getUserById($id);

                if (empty($user)) {
                    throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id, 'errors');
                }

                $cmsUsersTable->enableUser($id);
                
                //proveravamo da li je zahtev ajax ili ne
                $request instanceof Zend_Controller_Request_Http;
                
                if ($request->isXmlHttpRequest()) {
                    //request is ajax request
                    //send response as json
                    
                    $responseJson = array(
                        'status' => 'ok',
                        'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been enabled.', 
                        'success'
                    );
                    
                    $this->getHelper('Json')->sendJson($responseJson);
                }
                else {
                    $flashMessenger->addMessage('User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been enabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                }
                
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                if ($request->isXmlHttpRequest()) {
                    //request is ajax
                    $responseJson = array(
                        'status' => 'error',
                        'statusMessage' => $ex->getMessage()
                    );
                    //send json as response
                    $this->getHelper('Json')->sendJson($responseJson);
                }
                else {
                    $flashMessenger->addMessage($ex->getMessage(), 'errors');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_users',
                                'action' => 'index'
                                ), 'default', true);
                }
                
            }
        }
    

        public function resetpasswordAction() {
            $request = $this->getRequest();

            $flashMessenger = $this->getHelper('FlashMessenger');

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
            );
            if ($request->isPost() && $request->getPost('task') === 'resetPassword') {
                try {
                    // read $_POST['id]
                    $id = (int) $request->getPost('id');

                    if ($id <= 0) {
                        throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id);
                    }
                    $loggedInUser = Zend_Auth::getInstance()->getIdentity();
                    if ($id == $loggedInUser['id']) {
                        //redirect user to edit profile page
                        throw new Application_Model_Exception_InvalidInput('You can not reset password for your account!');
                    }
                    $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

                    $user = $cmsUsersTable->getUserById($id);

                    if (empty($user)) {
                        throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id);
                    }

                    $cmsUsersTable->changeUserPassword($id, Application_Model_DbTable_CmsUsers::DEFAULT_PASSWORD);
                    
                    //proveravamo da li je zahtev ajax ili ne
                    $request instanceof Zend_Controller_Request_Http;

                    if ($request->isXmlHttpRequest()) {
                        //request is ajax request
                        //send response as json

                        $responseJson = array(
                            'status' => 'ok',
                            'statusMessage' => 'Password has been reset for user ' . $user['first_name'] . ' ' . $user['last_name'] , 
                            'success'
                        );

                        $this->getHelper('Json')->sendJson($responseJson);

                    }
                    else {
                        $flashMessenger->addMessage('Password of user ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been reset', 'success');

                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_users',
                                    'action' => 'index'
                                        ), 'default', true);
                    }

                    
                } 
                catch (Application_Model_Exception_InvalidInput $ex) {
                    if ($request->isXmlHttpRequest()) {
                        //request is ajax
                        $responseJson = array(
                            'status' => 'error',
                            'statusMessage' => $ex->getMessage()
                        );
                        //send json as response
                        $this->getHelper('Json')->sendJson($responseJson);
                    }
                    else {
                        $flashMessenger->addMessage($ex->getMessage(), 'errors');
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_users',
                                    'action' => 'index'
                                        ), 'default', true);
                    }
                }
            }
            $this->view->systemMessages = $systemMessages;
            $this->view->form = $form;
        }
    
    
        public function datatableAction() {
        
            $request = $this->getRequest();

            $datatableParameteres = $request->getParams();

    //        print_r($datatableParameteres);
    //        die();

            /*
            Array
                (
                    [controller] => admin_users
                    [action] => datatable
                    [module] => default
                    [draw] => 1


                    [order] => Array
                        (
                            [0] => Array
                                (
                                    [column] => 2
                                    [dir] => asc
                                )

                        )

                    [start] => 0
                    [length] => 3
                    [search] => Array
                        (
                            [value] => 
                            [regex] => false
                        )


                )
             */


            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

            $loggedInUser = Zend_Auth::getInstance()->getIdentity();

            $filters = array(
                'id_exclude' => $loggedInUser
            );

            $orders = array();
            $limit = 5;
            $page = 1;
            $draw = 1;
            
            $columns = array('status', 'username', 'first_name', 'last_name', 'email','actions');

            //Proccess datatable parameters

            if (isset($datatableParameteres['draw'])) {
                
                $draw = $datatableParameteres['draw'];
                
                if (isset($datatableParameteres['length'])) {
                    //limit rows per page
                    $limit = $datatableParameteres['length'];
                    
                    if (isset($datatableParameteres['start'])) {
                        $page = floor($datatableParameteres['start'] / $datatableParameteres['length']) + 1;
                    }
                    
                }
                
                if (isset($datatableParameteres['order']) && is_array($datatableParameteres['order'])) {
                    foreach ($datatableParameteres['order'] as $datatableOrder) {
                        $columnIndex = $datatableOrder['column'];
                        $orderDirection = strtoupper($datatableOrder['dir']);
                        
                        if (isset($columns[$columnIndex])) {
                            $orders[$columns[$columnIndex]] = $orderDirection;
                        }
                        
                    }
                }
                
                if (isset($datatableParameteres['search']) && is_array($datatableParameteres['search']) && isset($datatableParameteres['search']['value'])) {
                    $filters['username_search'] = $datatableParameteres['search']['value'];
                }
                
            }

            $users = $cmsUsersTable->search(array(
                'filters' => $filters,
                'orders' => $orders,
                'limit' => $limit,
                'page' => $page
            ));

            $usersFilteredCount = $cmsUsersTable->count($filters);
            $usersTotal = $cmsUsersTable->count();

            $this->view->users = $users;
            $this->view->usersFilteredCount = $usersFilteredCount;
            $this->view->usersTotal = $usersTotal;
            $this->view->draw = $draw;
            $this->view->columns = $columns;
        
        }
        
        
        public function dashboardAction() {
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            
            //$filters['status'] = Application_Model_DbTable_CmsUsers::STATUS_ENABLED;
            //$usersActive = $cmsUsersTable->count($filters);
            
            $usersActive = $cmsUsersTable->count(array(
                'status' => Application_Model_DbTable_CmsUsers::STATUS_ENABLED
            ));
            $usersTotal = $cmsUsersTable->count();
            
//            $active = $cmsUsersTable->getActiveUsers();
//            $total = $cmsUsersTable->getTotalUsers();
            
            $this->view->active =  $usersActive;
            $this->view->total =  $usersTotal;
            
//            $this->view->active =  $active;
//            $this->view->total =  $total;
        }
        
        
        public function dashboard2Action() {
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            
            $active = $cmsUsersTable->getActiveUsers();
            $total = $cmsUsersTable->getTotalUsers();
            
            $this->view->active =  $active;
            $this->view->total =  $total;
        }
        
        
        public function dashboard3Action() {
            
            Zend_Layout::getMvcInstance()->disableLayout();
            
            //$this->getHelper("viewRenderer")->setNoRender(true);
            $this->_helper->viewRenderer->setNoRender(true);
            
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            
            $active = $cmsUsersTable->getActiveUsers();
            $total = $cmsUsersTable->getTotalUsers();
            
            
            echo $active . " / " . $total;
            
        }
        
        public function getstatsAction() {
            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
            
            $active = $cmsUsersTable->getActiveUsers();
            $total = $cmsUsersTable->getTotalUsers();
            
            $responseJson = new Application_Model_JsonResponse();
            
            $responseJson->setPayload(array(
                'active' => $active,
                'total' => $total
            ));
            
            $this->getHelper('Json')->sendJson($responseJson);
        }

    }

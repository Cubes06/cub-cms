<?php
    
    use Intervention\Image\ImageManagerStatic as Image;

    class Admin_MembersController extends Zend_Controller_Action {
        
        private $_widhtXL = 1060;
        private $_heightXL = 23;
      
        private $_widhtL = 748;
        private $_heightL = 748;
        
        private $_widhtS = 100;
        private $_heightS = 100;
        
        public function indexAction() {
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
            );
            
            // prikaz svih member-a
            $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
            
            
            
            // $select je od objekat klase Zend_Db_Select
//            $select = $cmsMembersDbTable->select();
//            $select->order('order_number ASC');
            
            
            //debug za db select - vrace se sql upit
            //die($select->assemble());
                   
//            $members = $cmsMembersDbTable->fetchAll($select);
            
			
			
			//ovo je deo za kesiranje
			$cache = Zend_Registry::get('mycache');
			$members = $cache->load('members');
			
			if (!$members) {
				$members = $cmsMembersDbTable->search(array(
					'filters' => array(
						//'status' => Application_Model_DbTable_CmsMembers::STATUS_DISABLED
						//'first_name_search' => 'Ale'
						//'first_name' => array('Aleksandra', 'Bojan')
						//'id' => array(1, 3, 5, 6)
					),
					'orders' => array(
						'order_number' => 'ASC'
					)
				));
				
				$cache->save($members, 'members');
			}
			
			
            $this->view->members = $members;
            $this->view->systemMessages = $systemMessages;
        }
        
        
        public function addAction() {
            
            $request = $this->getRequest(); //podaci iz url-a iz forme sa koje dolazimo 
            $flashMessenger = $this->getHelper('FlashMessenger');  // za prenosenje sistemskih poruka

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_MemberAdd();
            
            //default form data
            $form->populate(array(
                
            ));
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'save') {
                try {
                    //check form is valid
                    if (!$form->isValid($request->getPost())) {
                        throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new member');
                    }
                    
                    //get form data
                    $formData = $form->getValues();

                    //remove key member_photo from form data because there is no column 'member_photo' in cms_members table
                    unset($formData['member_photo']);
                    
                    $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

                    //insert member returns ID of the new member
                    $memberId = $cmsMembersTable->insertMember($formData);

                    if ($form->getElement('member_photo')->isUploaded()) {
                        //photo is uploaded

                        $fileInfos = $form->getElement('member_photo')->getFileInfo('member_photo');
                        $fileInfo = $fileInfos['member_photo'];


                        try {
                            //open uploaded photo in temporary directory
                            $memberPhoto = Image::make($fileInfo['tmp_name']);

                            $memberPhoto->fit(150, 150);

                            $memberPhoto->save(PUBLIC_PATH . '/uploads/members/' . $memberId . '.jpg');
                        }
                        catch (Exception $ex) {

                            $flashMessenger->addMessage('Member has been saved but error occured during image processing', 'errors');
                            //redirect to same or another page
                            $redirector = $this->getHelper('Redirector');
                            $redirector->setExit(true)
                                    ->gotoRoute(array(
                                        'controller' => 'admin_members',
                                        'action' => 'edit',
                                        'id' => $memberId
                                            ), 'default', true);
                        }
                        //$fileInfo = $_FILES['member_photo'];
                    }

                    $flashMessenger->addMessage('Member has been saved', 'success');
                    
                    //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
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
                throw new Zend_Controller_Router_Exception('Invalid member id: ' . $id, 404); // ovako prekidamo izvrsavanje programa i prikazujemo 'page not found'
            }
            
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
            $member = $cmsMembersTable->getMemberById($id);
            
            if (empty($member)) {
                throw new Zend_Controller_Router_Exception('No member is found with id: ' . $id, 404);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');  

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_MemberEdit();
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'update') {
                //default form data
                
                try {
                        //check form is valid
                        if (!$form->isValid($request->getPost())) {
                                throw new Application_Model_Exception_InvalidInput('Invalid data was sent for member');
                        }
                        
                        //get form data
                        $formData = $form->getValues();

                        unset($formData['member_photo']);

                        if ($form->getElement('member_photo')->isUploaded()) {
                            //photo is uploaded

                            $fileInfos = $form->getElement('member_photo')->getFileInfo('member_photo');
                            $fileInfo = $fileInfos['member_photo'];

                            try {
                                //open uploaded photo in temporary directory
                                $memberPhoto = Image::make($fileInfo['tmp_name']);

                                $memberPhoto->fit(150, 150);

                                $memberPhoto->save(PUBLIC_PATH . '/uploads/members/' . $member['id'] . '.jpg');

                            } 
                            catch (Exception $ex) {

                                    throw new Application_Model_Exception_InvalidInput('Error occured during image processing');

                            }
                            //$fileInfo = $_FILES['member_photo'];
                        }
                        
                        //Radimo update postojeceg zapisa u tabeli
                        $cmsMembersTable->updateMember($member['id'], $formData);

                        //set system message
                        $flashMessenger->addMessage('Member has been updated', 'success');
                        
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                   ->gotoRoute(
                                            array(
                                                'controller' => 'admin_members',
                                                'action' => 'index'
                                            ), 
                                           'default', 
                                           true
                        );
                }
                catch (Application_Model_Exception_InvalidInput $ex) {
                        $systemMessages['errors'][] = $ex->getMessage();
                }
            }
            else {
                //default form data
                $form->populate($member);
            }

            $this->view->systemMessages = $systemMessages;
            $this->view->form = $form;
            
            $this->view->member = $member;
            
	}
        
     
        public function deleteAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'delete') {
                // request is not post or task is not delete
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid member id: ' . $id);
                }

                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                $member = $cmsMembersTable->getMemberById($id);

                if (empty($member)) {
                    throw new Application_Model_Exception_InvalidInput('No member is found with id: ' . $id, 'errors');
                }

                $cmsMembersTable->deleteMember($id);
                $flashMessenger->addMessage('Member ' . $member['first_name'] . ' ' . $member['last_name'] . ' has been deleted.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);
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
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid member id: ' . $id);
                }

                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                $member = $cmsMembersTable->getMemberById($id);

                if (empty($member)) {
                    throw new Application_Model_Exception_InvalidInput('No member is found with id: ' . $id, 'errors');
                }

                $cmsMembersTable->disableMember($id);
                $flashMessenger->addMessage('Member ' . $member['first_name'] . ' ' . $member['last_name'] . ' has been disabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);
            }
            

            
        }
        
              
        public function enableAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'enable') {
                // request is not post or task is not disable
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid member id: ' . $id);
                }

                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                $member = $cmsMembersTable->getMemberById($id);

                if (empty($member)) {
                    throw new Application_Model_Exception_InvalidInput('No member is found with id: ' . $id, 'errors');
                }

                $cmsMembersTable->enableMember($id);
                $flashMessenger->addMessage('Member ' . $member['first_name'] . ' ' . $member['last_name'] . ' has been enabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'index'
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);
            }
            

            
        }
        
        
        public function updateorderAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
                // request is not post or task is not disable
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            
            try {
                
                $sortedIds = $request->getPost('sorted_ids');
                
                if (empty($sortedIds)) {
                    throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent.');
                }
                $sortedIds = trim($sortedIds, ' ,');
                if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                    throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
                }
                
                $sortedIds = explode(',', $sortedIds);
                
                
                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                
                $cmsMembersTable->updateOrderOfMembers($sortedIds);
                
                
                $flashMessenger->addMessage('Order is successfully saved', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);
            }
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);
            }
               
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                            ), 'default', true);          
        }
        
        
        public function dashboardAction() {
            
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
            
//            $active = $cmsMembersTable->getActiveMembers();
//            $total = $cmsMembersTable->getTotalMembers();
            
            $membersActive = $cmsMembersTable->count(array(
               'status' => Application_Model_DbTable_CmsMembers::STATUS_ENABLED
            ));
            $membersTotal = $cmsMembersTable->count();
            
            $this->view->active =  $membersActive;
            $this->view->total =  $membersTotal;
            
        }
        
        
        public function dashboard2Action() {
            
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
            
            $active = $cmsMembersTable->getActiveMembers();
            $total = $cmsMembersTable->getTotalMembers();
            
            $this->view->active =  $active;
            $this->view->total =  $total;
            
        }
        
        
        public function dashboard3Action() {
            
            Zend_Layout::getMvcInstance()->disableLayout();
            
            //$this->getHelper("viewRenderer")->setNoRender(true);
            $this->_helper->viewRenderer->setNoRender(true);
            
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
            
            $active = $cmsMembersTable->getActiveMembers();
            $total = $cmsMembersTable->getTotalMembers();
            
            
            echo $active . " / " . $total;
            
        }
        
        public function getstatsAction() {
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
            
            $active = $cmsMembersTable->getActiveMembers();
            $total = $cmsMembersTable->getTotalMembers();
            
            $responseJson = new Application_Model_JsonResponse();
            
            $responseJson->setPayload(array(
                'active' => $active,
                'total' => $total
            ));
            
            $this->getHelper('Json')->sendJson($responseJson);
        }
        
    }


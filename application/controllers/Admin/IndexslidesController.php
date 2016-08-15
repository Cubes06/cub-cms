<?php
    
    use Intervention\Image\ImageManagerStatic as Image;

    class Admin_IndexslidesController extends Zend_Controller_Action {
        
        public function indexAction() {
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
            );
            
            // prikaz svih indexSlide-a
            $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexslides();
            
            
            
            // $select je od objekat klase Zend_Db_Select
//            $select = $cmsIndexSlidesDbTable->select();
//            $select->order('order_number ASC');
            
            
            //debug za db select - vrace se sql upit
            //die($select->assemble());
                   
//            $indexSlides = $cmsIndexSlidesDbTable->fetchAll($select);
            
            $indexSlides = $cmsIndexSlidesDbTable->search(array(
                'filters' => array(
                    //'status' => Application_Model_DbTable_CmsIndexslides::STATUS_DISABLED
                    //'first_name_search' => 'Ale'
                    //'first_name' => array('Aleksandra', 'Bojan')
                    //'id' => array(1, 3, 5, 6)
                ),
                'orders' => array(
                    'order_number' => 'ASC'
                )
            ));
            
            $this->view->indexSlides = $indexSlides;
            $this->view->systemMessages = $systemMessages;
        }
        
        
        public function addAction() {
            
            $request = $this->getRequest(); //podaci iz url-a iz forme sa koje dolazimo 
            $flashMessenger = $this->getHelper('FlashMessenger');  // za prenosenje sistemskih poruka

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_IndexSlideAdd();
            
            //default form data
            $form->populate(array(
                
            ));
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'save') {
                try {
                    //check form is valid
                    if (!$form->isValid($request->getPost())) {
                        throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new index slide');
                    }
                    
                    //get form data
                    $formData = $form->getValues();

                    //remove key index_slide_photo form form data because there is no column 'index_slide_photo' in cms_indexSlides table
                    unset($formData['index_slide_photo']);
                    
                    $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();

                    //insert indexSlide returns ID of the new indexSlide
                    $indexSlideId = $cmsIndexSlidesTable->insertIndexSlide($formData);

                    if ($form->getElement('index_slide_photo')->isUploaded()) {
                        //photo is uploaded

                        $fileInfos = $form->getElement('index_slide_photo')->getFileInfo('index_slide_photo');
                        $fileInfo = $fileInfos['index_slide_photo'];


                        try {
                            //open uploaded photo in temporary directory
                            $indexSlidePhoto = Image::make($fileInfo['tmp_name']);

                            $indexSlidePhoto->fit(600, 400);

                            $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlideId . '.jpg');
                        }
                        catch (Exception $ex) {

                            $flashMessenger->addMessage('IndexSlide has been saved but error occured during image processing', 'errors');
                            //redirect to same or another page
                            $redirector = $this->getHelper('Redirector');
                            $redirector->setExit(true)
                                    ->gotoRoute(array(
                                        'controller' => 'admin_indexslides',
                                        'action' => 'edit',
                                        'id' => $indexSlideId
                                            ), 'default', true);
                        }
                        //$fileInfo = $_FILES['index_slide_photo'];
                    }

                    $flashMessenger->addMessage('IndexSlide has been saved', 'success');
                    
                    //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
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
                throw new Zend_Controller_Router_Exception('Invalid indexSlide id: ' . $id, 404); // ovako prekidamo izvrsavanje programa i prikazujemo 'page not found'
            }
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();
            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);
            
            if (empty($indexSlide)) {
                throw new Zend_Controller_Router_Exception('No indexSlide is found with id: ' . $id, 404);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');  

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_IndexSlideAdd();
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'update') {
                //default form data
                
                try {
                        //check form is valid
                        if (!$form->isValid($request->getPost())) {
                                throw new Application_Model_Exception_InvalidInput('Invalid data was sent for indexSlide');
                        }
                        
                        //get form data
                        $formData = $form->getValues();

                        unset($formData['index_slide_photo']);

                        if ($form->getElement('index_slide_photo')->isUploaded()) {
                            //photo is uploaded

                            $fileInfos = $form->getElement('index_slide_photo')->getFileInfo('index_slide_photo');
                            $fileInfo = $fileInfos['index_slide_photo'];

                            try {
                                //open uploaded photo in temporary directory
                                $indexSlidePhoto = Image::make($fileInfo['tmp_name']);

                                $indexSlidePhoto->fit(600, 400);

                                $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlide['id'] . '.jpg');

                            } 
                            catch (Exception $ex) {

                                    throw new Application_Model_Exception_InvalidInput('Error occured during image processing');

                            }
                            //$fileInfo = $_FILES['index_slide_photo'];
                        }
                        
                        //Radimo update postojeceg zapisa u tabeli
                        $cmsIndexSlidesTable->updateIndexSlide($indexSlide['id'], $formData);

                        //set system message
                        $flashMessenger->addMessage('Index Slide has been updated', 'success');
                        
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                   ->gotoRoute(
                                            array(
                                                'controller' => 'admin_indexslides',
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
                $form->populate($indexSlide);
            }

            $this->view->systemMessages = $systemMessages;
            $this->view->form = $form;
            
            $this->view->indexSlide = $indexSlide;
            
	}
        
     
        public function deleteAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'delete') {
                // request is not post or task is not delete
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid index Slide id: ' . $id);
                }

                $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();
                $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

                if (empty($indexSlide)) {
                    throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id: ' . $id, 'errors');
                }

                $cmsIndexSlidesTable->deleteIndexSlide($id);
                $flashMessenger->addMessage('Index slide ' . $indexSlide['title'] . ' has been deleted.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
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
                            'controller' => 'admin_indexslides',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id);
                }

                $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();
                $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

                if (empty($indexSlide)) {
                    throw new Application_Model_Exception_InvalidInput('No index slide is found with id: ' . $id, 'errors');
                }

                $cmsIndexSlidesTable->disableIndexSlide($id);
                $flashMessenger->addMessage('Index slide ' . $indexSlide['title'] . ' has been disabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
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
                            'controller' => 'admin_indexslides',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id);
                }

                $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();
                $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

                if (empty($indexSlide)) {
                    throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id: ' . $id, 'errors');
                }

                $cmsIndexSlidesTable->enableIndexSlide($id);
                $flashMessenger->addMessage('Index slide ' . $indexSlide['title'] . ' has been enabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
                                'action' => 'index'
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
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
                            'controller' => 'admin_indexslides',
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
                
                
                $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();
                
                $cmsIndexSlidesTable->updateOrderOfIndexSlides($sortedIds);
                
                
                $flashMessenger->addMessage('Order is successfully saved', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
                            'action' => 'index'
                            ), 'default', true);
            }
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
                            'action' => 'index'
                            ), 'default', true);
            }
               
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
                            'action' => 'index'
                            ), 'default', true);          
        }
        
        
        public function dashboardAction() {
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();
            
//            $active = $cmsIndexSlidesTable->getActiveIndexSlides();
//            $total = $cmsIndexSlidesTable->getTotalIndexSlides();
            
            $indexSlidesActive = $cmsIndexSlidesTable->count(array(
               'status' => Application_Model_DbTable_CmsIndexslides::STATUS_ENABLED
            ));
            $indexSlidesTotal = $cmsIndexSlidesTable->count();
            
            $this->view->active =  $indexSlidesActive;
            $this->view->total =  $indexSlidesTotal;
            
        }
        
        
        public function dashboard2Action() {
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();
            
            $active = $cmsIndexSlidesTable->getActiveIndexSlides();
            $total = $cmsIndexSlidesTable->getTotalIndexSlides();
            
            $this->view->active =  $active;
            $this->view->total =  $total;
            
        }
        
        
        public function dashboard3Action() {
            
            Zend_Layout::getMvcInstance()->disableLayout();
            
            //$this->getHelper("viewRenderer")->setNoRender(true);
            $this->_helper->viewRenderer->setNoRender(true);
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();
            
            $active = $cmsIndexSlidesTable->getActiveIndexSlides();
            $total = $cmsIndexSlidesTable->getTotalIndexSlides();
            
            
            echo $active . " / " . $total;
            
        }
        
        public function getstatsAction() {
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexslides();
            
            $active = $cmsIndexSlidesTable->getActiveIndexSlides();
            $total = $cmsIndexSlidesTable->getTotalIndexSlides();
            
            $responseJson = new Application_Model_JsonResponse();
            
            $responseJson->setPayload(array(
                'active' => $active,
                'total' => $total
            ));
            
            $this->getHelper('Json')->sendJson($responseJson);
        }
        
    }


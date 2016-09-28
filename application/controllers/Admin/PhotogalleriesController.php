<?php
    
    use Intervention\Image\ImageManagerStatic as Image;

    class Admin_PhotogalleriesController extends Zend_Controller_Action {
        
       
        
        public function indexAction() {
            
            $flashMessenger = $this->getHelper('FlashMessenger');
            
            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
            );
            
            // prikaz svih photoGallery-a
            $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
            
            
            
            // $select je od objekat klase Zend_Db_Select
//            $select = $cmsPhotoGalleriesDbTable->select();
//            $select->order('order_number ASC');
            
            
            //debug za db select - vrace se sql upit
            //die($select->assemble());
                   
//            $photoGalleries = $cmsPhotoGalleriesDbTable->fetchAll($select);
            
            $photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
                'filters' => array(
                    //'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_DISABLED
                    //'first_name_search' => 'Ale'
                    //'first_name' => array('Aleksandra', 'Bojan')
                    //'id' => array(1, 3, 5, 6)
                ),
                'orders' => array(
                    'order_number' => 'ASC'
                )
            ));
            
            $this->view->photoGalleries = $photoGalleries;
            $this->view->systemMessages = $systemMessages;
        }
        
        
//        public function addAction() {
//            
//            $request = $this->getRequest(); //podaci iz url-a iz forme sa koje dolazimo 
//            $flashMessenger = $this->getHelper('FlashMessenger');  // za prenosenje sistemskih poruka
//
//            $systemMessages = array(
//                'success' => $flashMessenger->getMessages('success'),
//                'errors' => $flashMessenger->getMessages('errors'),
//            );
//
//            $form = new Application_Form_Admin_PhotoGalleryAdd();
//            
//            //default form data
//            $form->populate(array(
//                
//            ));
//            
//            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
//            if ($request->isPost() && $request->getPost('task') === 'save') {
//                try {
//                    //check form is valid
//                    if (!$form->isValid($request->getPost())) {
//                        throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new photo gallery');
//                    }
//                    
//                    //get form data
//                    $formData = $form->getValues();
//
//                    //remove key photo_gallery_leading_photo from form data because there is no column 'photo_gallery_leading_photo' in cms_photoGalleries table
//                    unset($formData['photo_gallery_leading_photo']);
//                    
//                    $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//
//                    //insert photoGallery returns ID of the new photoGallery
//                    $photoGalleryId = $cmsPhotoGalleriesTable->insertPhotoGallery($formData);
//
//                    if ($form->getElement('photo_gallery_leading_photo')->isUploaded()) {
//                        //photo is uploaded
//
//                        $fileInfos = $form->getElement('photo_gallery_leading_photo')->getFileInfo('photo_gallery_leading_photo');
//                        $fileInfo = $fileInfos['photo_gallery_leading_photo'];
//
//
//                        try {
//                            //open uploaded photo in temporary directory
//                            $photoGalleryPhoto = Image::make($fileInfo['tmp_name']);
//
//                            $photoGalleryPhoto->fit(360, 270);
//
//                            $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGalleryId . '.jpg');
//                        }
//                        catch (Exception $ex) {
//
//                            $flashMessenger->addMessage('Photo gallery has been saved but error occured during image processing', 'errors');
//                            //redirect to same or another page
//                            $redirector = $this->getHelper('Redirector');
//                            $redirector->setExit(true)
//                                    ->gotoRoute(array(
//                                        'controller' => 'admin_photogalleries',
//                                        'action' => 'edit',
//                                        'id' => $photoGalleryId
//                                            ), 'default', true);
//                        }
//                        //$fileInfo = $_FILES['photo_gallery_leading_photo'];
//                    }
//
//                    $flashMessenger->addMessage('Photo gallery has been saved', 'success');
//                    
//                    //redirect to same or another page
//                    $redirector = $this->getHelper('Redirector');
//                    $redirector->setExit(true)
//                            ->gotoRoute(array(
//                                'controller' => 'admin_photogalleries',
//                                'action' => 'edit',
//                                'id' => $photoGalleryId
//                                    ), 'default', true);
//                } 
//                catch (Application_Model_Exception_InvalidInput $ex) {
//                    $systemMessages['errors'][] = $ex->getMessage();
//                }
//            }
//
//            $this->view->systemMessages = $systemMessages;
//            $this->view->form = $form;
//            
//        }//endf
//        
//        
//        public function editAction() {
//		
//	    $request = $this->getRequest();
//            
//            $id = (int) $request->getParam('id'); //(int) pretvara slova u nule
//            
//            if ($id <= 0) {
//                throw new Zend_Controller_Router_Exception('Invalid photo gallery id: ' . $id, 404); // ovako prekidamo izvrsavanje programa i prikazujemo 'page not found'
//            }
//            
//            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
//            
//            if (empty($photoGallery)) {
//                throw new Zend_Controller_Router_Exception('No photo gallery is found with id: ' . $id, 404);
//            }
//            
//            $flashMessenger = $this->getHelper('FlashMessenger');  
//
//            $systemMessages = array(
//                'success' => $flashMessenger->getMessages('success'),
//                'errors' => $flashMessenger->getMessages('errors'),
//            );
//
//            $form = new Application_Form_Admin_PhotoGalleryEdit();
//            
//            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
//            if ($request->isPost() && $request->getPost('task') === 'update') {
//                //default form data
//                
//                try {
//                        //check form is valid
//                        if (!$form->isValid($request->getPost())) {
//                                throw new Application_Model_Exception_InvalidInput('Invalid data was sent for photo gallery.');
//                        }
//                        
//                        //get form data
//                        $formData = $form->getValues();
//
//                        unset($formData['photo_gallery_leading_photo']);
//
//                        if ($form->getElement('photo_gallery_leading_photo')->isUploaded()) {
//                            //photo is uploaded
//
//                            $fileInfos = $form->getElement('photo_gallery_leading_photo')->getFileInfo('photo_gallery_leading_photo');
//                            $fileInfo = $fileInfos['photo_gallery_leading_photo'];
//
//                            try {
//                                //open uploaded photo in temporary directory
//                                $photoGalleryPhoto = Image::make($fileInfo['tmp_name']);
//
//                                $photoGalleryPhoto->fit(360, 270);
//
//                                $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGallery['id'] . '.jpg');
//
//                            } 
//                            catch (Exception $ex) {
//
//                                    throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
//
//                            }
//                            //$fileInfo = $_FILES['photo_gallery_leading_photo'];
//                        }
//                        
//                        //Radimo update postojeceg zapisa u tabeli
//                        $cmsPhotoGalleriesTable->updatePhotoGallery($photoGallery['id'], $formData);
//
//                        //set system message
//                        $flashMessenger->addMessage('Photo gallery has been updated', 'success');
//                        
//                        //redirect to same or another page
//                        $redirector = $this->getHelper('Redirector');
//                        $redirector->setExit(true)
//                                   ->gotoRoute(
//                                            array(
//                                                'controller' => 'admin_photogalleries',
//                                                'action' => 'index'
//                                            ), 
//                                           'default', 
//                                           true
//                        );
//                }
//                catch (Application_Model_Exception_InvalidInput $ex) {
//                        $systemMessages['errors'][] = $ex->getMessage();
//                }
//            }
//            else {
//                //default form data
//                $form->populate($photoGallery);
//            }
//
//            $cmsPhotosDbTable = new Application_Model_DbTable_CmsPhotos();
//            $photos = $cmsPhotosDbTable->search(array(
//                'filters' => array(
//                    'photo_gallery_id' => $photoGallery['id']
//                ),
//                'orders' => array(
//                    'order_number' => 'ASC'
//                )
//            ));
//            
//            
//            $this->view->systemMessages = $systemMessages;
//            $this->view->form = $form;
//            
//            $this->view->photoGallery = $photoGallery;
//            $this->view->photos = $photos;
//	}
//        
//     
//        public function deleteAction() {
//            
//            $request = $this->getRequest();
//            
//            if (!$request->isPost() || $request->getPost('task') != 'delete') {
//                // request is not post or task is not delete
//                // redirect to index page
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            }
//            
//            $flashMessenger = $this->getHelper('FlashMessenger'); 
//            
//            try {
//                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']
//
//                if ($id <= 0) {
//                    throw new Application_Model_Exception_InvalidInput('Invalid photoGallery id: ' . $id);
//                }
//
//                $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//                $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
//
//                if (empty($photoGallery)) {
//                    throw new Application_Model_Exception_InvalidInput('No photo gallery is found with id: ' . $id, 'errors');
//                }
//
//                $cmsPhotoGalleriesTable->deletePhotoGallery($id);
//                $flashMessenger->addMessage('Photo gallery ' . $photoGallery['title'] . ' has been deleted.', 'success');
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            } 
//            catch (Application_Model_Exception_InvalidInput $ex) {
//                $flashMessenger->addMessage($ex->getMessage(), 'errors');
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            }
//            
//        }
//        
//        
//        public function disableAction() {
//            
//            $request = $this->getRequest();
//            
//            if (!$request->isPost() || $request->getPost('task') != 'disable') {
//                // request is not post or task is not disable
//                // redirect to index page
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            }
//            
//            $flashMessenger = $this->getHelper('FlashMessenger'); 
//            
//            try {
//                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']
//
//                if ($id <= 0) {
//                    throw new Application_Model_Exception_InvalidInput('Invalid photo gallery id: ' . $id);
//                }
//
//                $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//                $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
//
//                if (empty($photoGallery)) {
//                    throw new Application_Model_Exception_InvalidInput('No photo gallery is found with id: ' . $id, 'errors');
//                }
//
//                $cmsPhotoGalleriesTable->disablePhotoGallery($id);
//                $flashMessenger->addMessage('Photo gallery ' . $photoGallery['title'] . ' has been disabled.', 'success');
//                    $redirector = $this->getHelper('Redirector');
//                    $redirector->setExit(true)
//                            ->gotoRoute(array(
//                                'controller' => 'admin_photogalleries',
//                                'action' => 'index'
//                                ), 'default', true);
//            } 
//            catch (Application_Model_Exception_InvalidInput $ex) {
//                $flashMessenger->addMessage($ex->getMessage(), 'errors');
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            }
//            
//
//            
//        }
//        
//              
//        public function enableAction() {
//            
//            $request = $this->getRequest();
//            
//            if (!$request->isPost() || $request->getPost('task') != 'enable') {
//                // request is not post or task is not disable
//                // redirect to index page
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            }
//            
//            $flashMessenger = $this->getHelper('FlashMessenger'); 
//            
//            try {
//                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']
//
//                if ($id <= 0) {
//                    throw new Application_Model_Exception_InvalidInput('Invalid photoGallery id: ' . $id);
//                }
//
//                $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//                $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);
//
//                if (empty($photoGallery)) {
//                    throw new Application_Model_Exception_InvalidInput('No photoGallery is found with id: ' . $id, 'errors');
//                }
//
//                $cmsPhotoGalleriesTable->enablePhotoGallery($id);
//                $flashMessenger->addMessage('Photo gallery ' . $photoGallery['title'] . ' has been enabled.', 'success');
//                    $redirector = $this->getHelper('Redirector');
//                    $redirector->setExit(true)
//                            ->gotoRoute(array(
//                                'controller' => 'admin_photogalleries',
//                                'action' => 'index'
//                                ), 'default', true);
//            } 
//            catch (Application_Model_Exception_InvalidInput $ex) {
//                $flashMessenger->addMessage($ex->getMessage(), 'errors');
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            }
//            
//
//            
//        }
//        
//        
//        public function updateorderAction() {
//            
//            $request = $this->getRequest();
//            
//            if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
//                // request is not post or task is not disable
//                // redirect to index page
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            }
//            
//            $flashMessenger = $this->getHelper('FlashMessenger'); 
//            
//            
//            try {
//                
//                $sortedIds = $request->getPost('sorted_ids');
//                
//                if (empty($sortedIds)) {
//                    throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent.');
//                }
//                $sortedIds = trim($sortedIds, ' ,');
//                if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
//                    throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
//                }
//                
//                $sortedIds = explode(',', $sortedIds);
//                
//                
//                $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//                
//                $cmsPhotoGalleriesTable->updateOrderOfPhotoGalleries($sortedIds);
//                
//                
//                $flashMessenger->addMessage('Order is successfully saved', 'success');
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            }
//            catch (Application_Model_Exception_InvalidInput $ex) {
//                $flashMessenger->addMessage($ex->getMessage(), 'errors');
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);
//            }
//               
//            $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_photogalleries',
//                            'action' => 'index'
//                            ), 'default', true);          
//        }
//        
//        
//        public function dashboardAction() {
//            
//            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//            
////            $active = $cmsPhotoGalleriesTable->getActivePhotoGalleries();
////            $total = $cmsPhotoGalleriesTable->getTotalPhotoGalleries();
//            
//            $photoGalleriesActive = $cmsPhotoGalleriesTable->count(array(
//               'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED
//            ));
//            $photoGalleriesTotal = $cmsPhotoGalleriesTable->count();
//            
//            $this->view->active =  $photoGalleriesActive;
//            $this->view->total =  $photoGalleriesTotal;
//            
//        }
//        
//        
//        public function dashboard2Action() {
//            
//            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//            
//            $active = $cmsPhotoGalleriesTable->getActivePhotoGalleries();
//            $total = $cmsPhotoGalleriesTable->getTotalPhotoGalleries();
//            
//            $this->view->active =  $active;
//            $this->view->total =  $total;
//            
//        }
//        
//        
//        public function dashboard3Action() {
//            
//            Zend_Layout::getMvcInstance()->disableLayout();
//            
//            //$this->getHelper("viewRenderer")->setNoRender(true);
//            $this->_helper->viewRenderer->setNoRender(true);
//            
//            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//            
//            $active = $cmsPhotoGalleriesTable->getActivePhotoGalleries();
//            $total = $cmsPhotoGalleriesTable->getTotalPhotoGalleries();
//            
//            
//            echo $active . " / " . $total;
//            
//        }
//        
//        public function getstatsAction() {
//            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
//            
//            $active = $cmsPhotoGalleriesTable->getActivePhotoGalleries();
//            $total = $cmsPhotoGalleriesTable->getTotalPhotoGalleries();
//            
//            $responseJson = new Application_Model_JsonResponse();
//            
//            $responseJson->setPayload(array(
//                'active' => $active,
//                'total' => $total
//            ));
//            
//            $this->getHelper('Json')->sendJson($responseJson);
//        }
//        
    }


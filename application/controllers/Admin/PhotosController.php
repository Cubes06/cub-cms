<?php
    
    use Intervention\Image\ImageManagerStatic as Image;

    class Admin_PhotosController extends Zend_Controller_Action {
    
        public function addAction() {
            
            $request = $this->getRequest(); //podaci iz url-a iz forme sa koje dolazimo 
            
            $photoGalleryId = (int) $request->getParam('photo_gallery_id'); //(int) pretvara slova u nule
            
            if ($photoGalleryId <= 0) {
                throw new Zend_Controller_Router_Exception('Invalid photo gallery id: ' . $photoGalleryId, 404); // ovako prekidamo izvrsavanje programa i prikazujemo 'page not found'
            }
            
            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($photoGalleryId);
            
            if (empty($photoGallery)) {
                throw new Zend_Controller_Router_Exception('No photo gallery is found with id: ' . $photoGalleryId, 404);
            }
            
            
            
            $flashMessenger = $this->getHelper('FlashMessenger');  // za prenosenje sistemskih poruka

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_PhotoAdd();
            
            //default form data
            $form->populate(array(
                
            ));
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'save') {
                try {
                    //check form is valid
                    if (!$form->isValid($request->getPost())) {
                        throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new photo');
                    }
                    
                    //get form data
                    $formData = $form->getValues();

                    //remove key photo_upload from form data because there is no column 'photo_upload' in cms_photos table
                    unset($formData['photo_upload']);
                    $formData['photo_gallery_id'] = $photoGallery['id'];
                    
                    $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();

                    //insert photo returns ID of the new photo
                    $photoId = $cmsPhotosTable->insertPhoto($formData);

                    if ($form->getElement('photo_upload')->isUploaded()) {
                        //photo is uploaded

                        $fileInfos = $form->getElement('photo_upload')->getFileInfo('photo_upload');
                        $fileInfo = $fileInfos['photo_upload'];


                        try {
                            //open uploaded photo in temporary directory
                            $photoPhoto = Image::make($fileInfo['tmp_name']);

                            $photoPhoto->fit(660, 495);

                            $photoPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/photos/' . $photoId . '.jpg');
                        }
                        catch (Exception $ex) {

                            $flashMessenger->addMessage('Photo has been saved but error occured during image processing' . $ex->getMessage(), 'errors');
                            //redirect to same or another page
                            $redirector = $this->getHelper('Redirector');
                            $redirector->setExit(true)
                                    ->gotoRoute(array(
                                        'controller' => 'admin_photogalleries',
                                        'action' => 'edit',
                                        'id' => $photoGallery['id']
                                            ), 'default', true);
                        }
                        //$fileInfo = $_FILES['photo_upload'];
                    }

                    $flashMessenger->addMessage('Photo has been saved', 'success');
                    
                    //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photoGallery['id']
                                    ), 'default', true);
                } 
                catch (Application_Model_Exception_InvalidInput $ex) {
                    $flashMessenger->addMessage($ex->getMessage(), 'errors');
                            //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photoGallery['id']
                                    ), 'default', true);
                }
            }
            
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'edit',
                        'id' => $photoGallery['id']
                            ), 'default', true);

        }//endf
        
        
        public function editAction() {
		
	    $request = $this->getRequest();
            
            $id = (int) $request->getParam('id'); //(int) pretvara slova u nule
            
            if ($id <= 0) {
                throw new Zend_Controller_Router_Exception('Invalid photo id: ' . $id, 404); // ovako prekidamo izvrsavanje programa i prikazujemo 'page not found'
            }
            
            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
            $photo = $cmsPhotosTable->getPhotoById($id);
            
            if (empty($photo)) {
                throw new Zend_Controller_Router_Exception('No photo is found with id: ' . $id, 404);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');  

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_PhotoEdit();
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'update') {
                //default form data
                
                try {
                        //check form is valid
                        if (!$form->isValid($request->getPost())) {
                                throw new Application_Model_Exception_InvalidInput('Invalid data was sent for photo');
                        }
                        
                        //get form data
                        $formData = $form->getValues();

                        
                        //Radimo update postojeceg zapisa u tabeli
                        $cmsPhotosTable->updatePhoto($photo['id'], $formData);

                        //set system message
                        $flashMessenger->addMessage('Photo has been updated', 'success');
                        
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                   ->gotoRoute(
                                            array(
                                                'controller' => 'admin_photogalleries',
                                                'action' => 'edit',
                                                'id' => $photo['photo_gallery_id']
                                            ), 
                                           'default', 
                                           true
                        );
                }
                catch (Application_Model_Exception_InvalidInput $ex) {
                    $flashMessenger->addMessage($ex->getMessage(), 'errors');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photo['photo_gallery_id']
                            ), 'default', true);
                }
            }
            else {
                //default form data
                $form->populate($photo);
            }
            
            $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photo['photo_gallery_id']
                            ), 'default', true);

            
	}//endf
        
     
        public function deleteAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'delete') {
                // request is not post or task is not delete
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                        ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid photo id: ' . $id);
                }

                $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                $photo = $cmsPhotosTable->getPhotoById($id);

                if (empty($photo)) {
                    throw new Application_Model_Exception_InvalidInput('No photo is found with id: ' . $id, 'errors');
                }

                $cmsPhotosTable->deletePhoto($id);
                $flashMessenger->addMessage('Photo has been deleted.', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photo['photo_gallery_id']
                        ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
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
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid photo id: ' . $id);
                }

                $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                $photo = $cmsPhotosTable->getPhotoById($id);

                if (empty($photo)) {
                    throw new Application_Model_Exception_InvalidInput('No photo is found with id: ' . $id, 'errors');
                }

                $cmsPhotosTable->disablePhoto($id);
                $flashMessenger->addMessage('Photo has been disabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photo['photo_gallery_id']
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
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
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid photo id: ' . $id);
                }

                $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                $photo = $cmsPhotosTable->getPhotoById($id);

                if (empty($photo)) {
                    throw new Application_Model_Exception_InvalidInput('No photo is found with id: ' . $id, 'errors');
                }

                $cmsPhotosTable->enablePhoto($id);
                $flashMessenger->addMessage('Photo has been enabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photo['photo_gallery_id']
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                            ), 'default', true);
            }
          
        }//endf
        
        
        public function updateorderAction() {
            
            $request = $this->getRequest();
            
            $photoGalleryId = (int) $request->getParam('photo_gallery_id'); //(int) pretvara slova u nule
            
            if ($id <= 0) {
                throw new Zend_Controller_Router_Exception('Invalid photo gallery id: ' . $photoGalleryId, 404); // ovako prekidamo izvrsavanje programa i prikazujemo 'page not found'
            }
            
            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($photoGalleryId);
            
            if (empty($photoGallery)) {
                throw new Zend_Controller_Router_Exception('No photo gallery is found with id: ' . $photoGalleryId, 404);
            }
            
            if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
                // request is not post or task is not disable
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
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
                
                
                $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                
                $cmsPhotosTable->updateOrderOfPhotos($sortedIds);
                
                
                $flashMessenger->addMessage('Order is successfully saved', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photoGallery['Id']
                            ), 'default', true);
            }
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                            ), 'default', true);
            }
               
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                            ), 'default', true);          
        }
        
        
        public function dashboardAction() {
            
            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
            
//            $active = $cmsPhotosTable->getActivePhotos();
//            $total = $cmsPhotosTable->getTotalPhotos();
            
            $photosActive = $cmsPhotosTable->count(array(
               'status' => Application_Model_DbTable_CmsPhotos::STATUS_ENABLED
            ));
            $photosTotal = $cmsPhotosTable->count();
            
            $this->view->active =  $photosActive;
            $this->view->total =  $photosTotal;
            
        }
        
        
        public function dashboard2Action() {
            
            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
            
            $active = $cmsPhotosTable->getActivePhotos();
            $total = $cmsPhotosTable->getTotalPhotos();
            
            $this->view->active =  $active;
            $this->view->total =  $total;
            
        }
        
        
        public function dashboard3Action() {
            
            Zend_Layout::getMvcInstance()->disableLayout();
            
            //$this->getHelper("viewRenderer")->setNoRender(true);
            $this->_helper->viewRenderer->setNoRender(true);
            
            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
            
            $active = $cmsPhotosTable->getActivePhotos();
            $total = $cmsPhotosTable->getTotalPhotos();
            
            
            echo $active . " / " . $total;
            
        }
        
        public function getstatsAction() {
            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
            
            $active = $cmsPhotosTable->getActivePhotos();
            $total = $cmsPhotosTable->getTotalPhotos();
            
            $responseJson = new Application_Model_JsonResponse();
            
            $responseJson->setPayload(array(
                'active' => $active,
                'total' => $total
            ));
            
            $this->getHelper('Json')->sendJson($responseJson);
        }
        
    }


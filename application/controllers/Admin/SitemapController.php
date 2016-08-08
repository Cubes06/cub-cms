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
            
            if ($parentId != 0) {
                $parentSitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($parentId);
            
                if (!$parentSitemapPage) {
                    throw new Zend_Controller_Router_Exception('No parent is found for sitemap pages', 404);
                }
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
            
            $this->view->currentSitemapPageId = $parentId;
            $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
            $this->view->sitemapPages = $sitemapPages;
            $this->view->systemMessages = $systemMessages;



        }//endf
        
        
        public function addAction() {
                
            $request = $this->getRequest(); //podaci iz url-a iz forme sa koje dolazimo 
            $parentId = (int) $request->getParam('parent_id', 0);  //0 po default-u
            
            if ($parentId < 0) {
                throw new Zend_Controller_Router_Exception('Invalid parent id for sitemap pages', 404);
            }
            
            $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
            
            if ($parentId != 0) {
                $parentSitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($parentId);
                
                if (!$parentSitemapPage) {
                    throw new Zend_Controller_Router_Exception('No sitemap page is found for id: '. $parentId, 404);
                }
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');  // za prenosenje sistemskih poruka

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_SitemapPageAdd($parentId);
            //set parent_id for new page
            //
            
            //default form data
            $form->populate(array(
                
            ));
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'save') {
                try {
                    //check form is valid
                    if (!$form->isValid($request->getPost())) {
                        throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new sitemap page');
                    }
                    
                    //get form data
                    $formData = $form->getValues();
                    $formData['parent_id'] = $parentId;
                    //remove key sitemapPage_photo form form data because there is no column 'sitemapPage_photo' in cms_sitemapPage table
                    //unset($formData['sitemapPage_photo']);
                    

                    //insert sitemapPage returns ID of the new sitemapPage
                    $sitemapPageId = $cmsSitemapPagesDbTable->insertSitemapPage($formData);

//                    if ($form->getElement('sitemap_page_photo')->isUploaded()) {
//                        //photo is uploaded
//
//                        $fileInfos = $form->getElement('sitemapPage_photo')->getFileInfo('sitemapPage_photo');
//                        $fileInfo = $fileInfos['sitemapPage_photo'];
//
//
//                        try {
//                            //open uploaded photo in temporary directory
//                            $sitemapPagePhoto = Image::make($fileInfo['tmp_name']);
//
//                            $sitemapPagePhoto->fit(150, 150);
//
//                            $sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPage/' . $sitemapPageId . '.jpg');
//                        }
//                        catch (Exception $ex) {
//
//                            $flashMessenger->addMessage('SitemapPage has been saved but error occured during image processing', 'errors');
//                            //redirect to same or another page
//                            $redirector = $this->getHelper('Redirector');
//                            $redirector->setExit(true)
//                                    ->gotoRoute(array(
//                                        'controller' => 'admin_sitemapPage',
//                                        'action' => 'edit',
//                                        'id' => $sitemapPageId
//                                            ), 'default', true);
//                        }
//                        //$fileInfo = $_FILES['sitemapPage_photo'];
//                    }

                    $flashMessenger->addMessage('Sitemap Page has been saved', 'success');
                    
                    //redirect to same or another page
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'parent_id' => $parentId
                                    ), 'default', true);
                } 
                catch (Application_Model_Exception_InvalidInput $ex) {
                    $systemMessages['errors'][] = $ex->getMessage();
                }
            }
            
            $sitemapPageBreadcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBreadcrumbs($parentId);
             
            $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
            $this->view->parentId = $parentId;
            $this->view->systemMessages = $systemMessages;
            $this->view->form = $form;
            
        }//endf
        
        
        public function editAction() {
            $request = $this->getRequest();
            
            $id = (int) $request->getParam('id'); //(int) pretvara slova u nule
            
            if ($id <= 0) {
                throw new Zend_Controller_Router_Exception('Invalid sitemapPage id: ' . $id, 404); // ovako prekidamo izvrsavanje programa i prikazujemo 'page not found'
            }
            
            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
            
            if (empty($sitemapPage)) {
                throw new Zend_Controller_Router_Exception('No sitemapPage is found with id: ' . $id, 404);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger');  

            $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors'),
            );

            $form = new Application_Form_Admin_SitemapPageEdit($sitemapPage['id'], $sitemapPage['parent_id']);
            
            $form->populate($sitemapPage);
            
            // kad prvi put dolazimo onda je get method, a ako smo preko forme onda je post method
            if ($request->isPost() && $request->getPost('task') === 'update') {
                //default form data
                
                try {
                        //check form is valid
                        if (!$form->isValid($request->getPost())) {
                                throw new Application_Model_Exception_InvalidInput('Invalid data was sent for sitemapPage');
                        }
                        
                        //get form data
                        $formData = $form->getValues();

                        
                        //Radimo update postojeceg zapisa u tabeli
                        $cmsSitemapPagesTable->updateSitemapPage($sitemapPage['id'], $formData);

                        //set system message
                        $flashMessenger->addMessage('SitemapPage has been updated', 'success');
                        
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                   ->gotoRoute(
                                            array(
                                                'controller' => 'admin_sitemap',
                                                'action' => 'index',
                                                'id' => $sitemapPage['parent_id']
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
                $form->populate($sitemapPage);
            }

            $sitemapPageBreadcrumbs = $cmsSitemapPagesTable->getSitemapPageBreadcrumbs($sitemapPage['parent_id']);
             
            $this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
            $this->view->systemMessages = $systemMessages;
            $this->view->form = $form;
            
            $this->view->sitemapPage = $sitemapPage;
            
        }
        
              
        public function enableAction() {
            
            $request = $this->getRequest();
            
            
            if (!$request->isPost() || $request->getPost('task') != 'enable') {
                // request is not post or task is not disable
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'parent_id' => ''
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid sitemap id: ' . $id);
                }

                $cmsSitemapsTable = new Application_Model_DbTable_CmsSitemapPages();
                $sitemap = $cmsSitemapsTable->getSitemapPageById($id);

                if (empty($sitemap)) {
                    throw new Application_Model_Exception_InvalidInput('No sitemap is found with id: ' . $id, 'errors');
                }

                $cmsSitemapsTable->enableSitemapPage($id);
                $flashMessenger->addMessage('Sitemap ' . $sitemap['title'] . ' has been enabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'parent_id' => $sitemap['parent_id']
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'parent_id' => $sitemap['parent_id']
                            ), 'default', true);
            }
            
        }//endf
        
        
        
        public function disableAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'disable') {
                // request is not post or task is not disable
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'parent_id' => ''
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid sitemap id: ' . $id);
                }

                $cmsSitemapsTable = new Application_Model_DbTable_CmsSitemapPages();
                $sitemap = $cmsSitemapsTable->getSitemapPageById($id);

                if (empty($sitemap)) {
                    throw new Application_Model_Exception_InvalidInput('No sitemap is found with id: ' . $id, 'errors');
                }

                $cmsSitemapsTable->disableSitemapPage($id);
                $flashMessenger->addMessage('Sitemap ' . $sitemap['title'] . ' has been disabled.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'parent_id' => $sitemap['parent_id']
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'parent_id' => $sitemap['parent_id']
                            ), 'default', true);
            }
            

            
        }
        
        
        public function deleteAction() {
            
            $request = $this->getRequest();
            
            if (!$request->isPost() || $request->getPost('task') != 'delete') {
                // request is not post or task is not delete
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'parent_id' => ''
                            ), 'default', true);
            }
            
            $flashMessenger = $this->getHelper('FlashMessenger'); 
            
            try {
                $id = (int) $request->getPost('id'); // isto sto i read $_POST['id']

                if ($id <= 0) {
                    throw new Application_Model_Exception_InvalidInput('Invalid sitemap id: ' . $id);
                }

                $cmsSitemapTable = new Application_Model_DbTable_CmsSitemapPages();
                $sitemap = $cmsSitemapTable->getSitemapPageById($id);

                if (empty($sitemap)) {
                    throw new Application_Model_Exception_InvalidInput('No sitemap is found with id: ' . $id, 'errors');
                }

                $cmsSitemapTable->deleteSitemapPage($id);
                $flashMessenger->addMessage('Sitemap ' . $sitemap['title'] . ' has been deleted.', 'success');
                    $redirector = $this->getHelper('Redirector');
                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_sitemap',
                                'action' => 'index',
                                'parent_id' => $sitemap['parent_id']
                                ), 'default', true);
            } 
            catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'parent_id' => $sitemap['parent_id']
                            ), 'default', true);
            }
            
        }
        
        
         
        public function updateorderAction() {

            $request = $this->getRequest();

            if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
                // request is not post or task is not saveOrder
                // redirect to index page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'parent_id' => ''
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


                $cmsSitemapTable = new Application_Model_DbTable_CmsSitemapPages();

                $cmsSitemapTable->updateOrderOfSitemap($sortedIds);


                $flashMessenger->addMessage('Order is successfully saved', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'parent_id' => $sitemap['parent_id']
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index'
                                ), 'default', true);
            }

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index'
                            ), 'default', true);
        }

} //end of class: Admin_SitemapController


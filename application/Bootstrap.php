<?php

    class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
        //ovo nema veze sa framework-om 
        //ovo je konfiguracija aplikacije u procesu bootstrap-inga
        
        protected function _initRouter() {  //bitno je da pocinje sa donjom crtom i init (_initPaNesto)
            //prvo mora da se ucita konfiguracija za bazu
            //ensure that database is configured
            $this->bootstrap('db');
            
            //ovde definisemo koji sve tipovi stranica postoje
            $sitemapPageTypes = array(
                'StaticPage' => array(
                    'title' => 'Static Page',
                    'subtypes' => array(
                        // 0 means unlimited number
                        'StaticPage' => 0
                    )
                ),
                'AboutUsPage' => array(
                    'title' => 'About Us Page',
                    'subtypes' => array(
                        
                    )
                ),
                'ServicesPage' => array(
                    'title' => 'Services Page',
                    'subtypes' => array(
                        
                    )
                ),
                'ContactPage' => array(
                    'title' => 'Contact Page',
                    'subtypes' => array(
                        
                    )
                ),
                'DogsPage' => array(
                    'title' => 'Dogs Page',
                    'subtypes' => array(
                        'DogsPage' => 0
                    )
                )
            );
            
            //ovde definisemo koje stranice mogu da se nalaze u root-u i koliko puta
            $rootSitemapPageTypes = array(
                'StaticPage' => 0,
                'AboutUsPage' => 1,
                'ServicesPage' => 1,
                'ContactPage' => 1,
                'DogsPage' => 0,
            );
            
            //ovo je neke vreca...
            Zend_Registry::set('sitemapPageTypes', $sitemapPageTypes);
            Zend_Registry::set('rootSitemapPageTypes', $rootSitemapPageTypes);
            
            
            //centralna klasa
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $router instanceof Zend_Controller_Router_Rewrite;
            //dodajemo rute, poslednja ruta ima veci prioritet.
            $router->addRoute( 
                'contact-us-route', 
                new Zend_Controller_Router_Route_Static (
                        'contact-us',
                        array(
                            'controller' => 'contact',
                            'action' => 'index'
                        )
                )
            )->addRoute(
                'ask-member-route', 
                new Zend_Controller_Router_Route (
                        'ask-member/:id/:member_slug',
                        array(
                            'controller' => 'contact',
                            'action' => 'askmember',
                            'member_slug' => ''
                        )
                )
            );
            
            $sitemapPagesMap = Application_Model_DbTable_CmsSitemapPages::getSitemapPagesMap();
            //print_r($sitemapPagesMap);die();
            foreach ($sitemapPagesMap as $sitemapPageId => $sitemapPageMap) {
                
                if ($sitemapPageMap['type'] == 'StaticPage') {
                    $router->addRoute( 
                        'static-page-route-' . $sitemapPageId, 
                        new Zend_Controller_Router_Route_Static (
                                $sitemapPageMap['url'],
                                array(
                                    'controller' => 'staticpage',
                                    'action' => 'index',
                                    'sitemap_page_id' => $sitemapPageId
                                )
                        )
                    );
                }
                
                
                if ($sitemapPageMap['type'] == 'AboutUsPage') {
                    $router->addRoute( 
                        'static-page-route-' . $sitemapPageId, 
                        new Zend_Controller_Router_Route_Static (
                                $sitemapPageMap['url'],
                                array(
                                    'controller' => 'aboutus',
                                    'action' => 'index',
                                    'sitemap_page_id' => $sitemapPageId
                                )
                        )
                    );
                    
                    $router->addRoute(
                        'member-route', 
                        new Zend_Controller_Router_Route (
                                $sitemapPageMap['url'] . '/member/:id/:member_slug',
                                array(
                                    'controller' => 'aboutus',
                                    'action' => 'member',
                                    'member_slug' => ''
                                )
                        )
                    );
                }
                
                if ($sitemapPageMap['type'] == 'ContactPage') {
                    $router->addRoute( 
                        'static-page-route-' . $sitemapPageId, 
                        new Zend_Controller_Router_Route_Static (
                                $sitemapPageMap['url'],
                                array(
                                    'controller' => 'contact',
                                    'action' => 'index',
                                    'sitemap_page_id' => $sitemapPageId
                                )
                        )
                    );
                }
                
                if ($sitemapPageMap['type'] == 'DogsPage') {
                    $router->addRoute( 
                        'static-page-route-' . $sitemapPageId, 
                        new Zend_Controller_Router_Route_Static (
                                $sitemapPageMap['url'],
                                array(
                                    'controller' => 'dogs',
                                    'action' => 'index',
                                    'sitemap_page_id' => $sitemapPageId
                                )
                        )
                    );
                }
                
            }
            
            
        }//endf
        
        
    } //end of class Bootstrap

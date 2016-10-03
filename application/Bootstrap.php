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
                ),
                'PhotoGalleriesPage' => array(
                    'title' => 'PhotoGalleries Page',
                    'subtypes' => array(
                        
                    )
                ),
            );
            
            //ovde definisemo koje stranice mogu da se nalaze u root-u i koliko puta
            $rootSitemapPageTypes = array(
                'StaticPage' => 0,
                'AboutUsPage' => 1,
                'ServicesPage' => 1,
                'ContactPage' => 1,
                'DogsPage' => 0,
                'PhotoGalleriesPage' => 1
            );
            
            //preporucljivoje setovati samo ovde u bootstrap.php fajlu
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
                
                if ($sitemapPageMap['type'] == 'ServicesPage') {
                    $router->addRoute( 
                        'static-page-route-' . $sitemapPageId, 
                        new Zend_Controller_Router_Route_Static (
                                $sitemapPageMap['url'],
                                array(
                                    'controller' => 'services',
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
                
                if ($sitemapPageMap['type'] == 'PhotoGalleriesPage') {
                    $router->addRoute( 
                        'static-page-route-' . $sitemapPageId, 
                        new Zend_Controller_Router_Route_Static (
                                $sitemapPageMap['url'],
                                array(
                                    'controller' => 'photogalleries',
                                    'action' => 'index',
                                    'sitemap_page_id' => $sitemapPageId
                                )
                        )
                    );
                    
                    $router->addRoute(
                        'photo-gallery-route', 
                        new Zend_Controller_Router_Route (
                                $sitemapPageMap['url'] . '/:id/:photo_gallery_slug',
                                array(
                                    'controller' => 'photogalleries',
                                    'action' => 'gallery',
                                    'sitemap_page_id' => $sitemapPageId
                                )
                        )
                    );
                }
                
            }
            
            
        }//endf
        
        // cache bootstrap
		protected function _initCache () {
			$frontEndOptions = array(
				'lifetime' => 10,  //ko dodje u tih 10 sekundi dobija iste podatke, vuce ih sa fajl sistema
				'automatic_serialization' => true
			);

			if (!file_exists( "../cache" )) {
				mkdir( "../cache", 0777, true);
			}

			$backEndOptions = array('cache_dir' => PUBLIC_PATH . "/../cache");
			
			// Get a Zend_Cache_Core object
			$cache = Zend_Cache::factory('Core',
										 'File',
										 $frontEndOptions,
										 $backEndOptions
			);
			Zend_Registry::set('mycache', $cache);
		} //endf _initCache()
		
		
		// translate
		protected function _initTranslate() {
			
			$translate = new Zend_Translate (
				array(
					'adapter' => 'array',
					'content' => APPLICATION_PATH . '/translate/language/en.php',
					'locale' => 'en'
				)
			);

			$translate->addTranslation(
				array(
					'adapter' => 'array',
					'content' => APPLICATION_PATH . '/translate/language/sr.php',
					'locale' => 'sr'
				)
			);

			$translate->setLocale('en');

			Zend_Registry::set('Zend_Translate', $translate);
		}

		
		
    } //end of class Bootstrap

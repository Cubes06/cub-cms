<?php

    class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
        //ovo nema veze sa framework-om 
        //ovo je konfiguracija aplikacije u procesu bootstrap-inga
        
        protected function _initRouter() {  //bitno je da pocinje sa donjom crtom i init (_initPaNesto)
            
            //centralna klasa
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $router instanceof Zend_Controller_Router_Rewrite;
            //dodajemo rute, poslednja ruta ima veci prioritet.
            $router->addRoute('about-us-route', new Zend_Controller_Router_Route_Static(
                            'about-us',
                            array(
                                'controller' => 'aboutus',
                                'action' => 'index'
                            )
                    ))->addRoute('member-route', new Zend_Controller_Router_Route(
                            'about-us/member/:id/:member_slug',
                            array(
                                'controller' => 'aboutus',
                                'action' => 'member',
                                'member_slug' => ''
                            )
                    ));
            
            
            
        }//endf
        
        
    } //end of class Bootstrap

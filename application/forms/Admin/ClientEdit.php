<?php

    class Application_Form_Admin_ClientEdit extends Zend_Form {


        public function init() {

            $name = new Zend_Form_Element_Text('name');
            $name->addFilter('StringTrim')
                    ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                    ->setRequired(true);
            $this->addElement($name);

            $description = new Zend_Form_Element_Text('description');
            $description->addFilter('StringTrim')
                    ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                    ->setRequired(FALSE);
            $this->addElement($description);


            $clientPhoto = new Zend_Form_Element_File('client_photo');
            $clientPhoto->addValidator('Count', true, 1)//ogranicavamo broj fajlova koji se mogu uploud-ovati 
                        ->addValidator('MimeType', true, array('image/jpeg', 'image/gif', 'image/png'))
                        ->addValidator('ImageSize', false, array(
                            'minwidth' => 170,
                            'minheight' => 70,
                            'maxwidth' => 2000,
                            'maxheight' => 2000
                        ))
                        ->addValidator('Size', false, array(
                            'max' => '10MB'
                        ))
                        // disable move file to destination when calling method getValues
                        ->setValueDisabled(true)
                        ->setRequired(false);

            $this->addElement($clientPhoto);


        }//endf init

    } //end of: class Application_Form_Admin_ClientEdit

<?php

    class Application_Form_Admin_PhotoEdit extends Zend_Form {

        
        public function init() {
            
            $title = new Zend_Form_Element_Text('title');
            $title->addFilter('StringTrim')
                    ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                    ->setRequired(false);
            $this->addElement($title);

            
            $description = new Zend_Form_Element_Textarea('description');
            $description->addFilter('StringTrim')
                    ->setRequired(false);
            $this->addElement($description);


        }//endf init


    } //end of: class Application_Form_Admin_MemberAdd
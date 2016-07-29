<?php

    class Application_Form_Admin_MemberEdit extends Zend_Form {

        
        public function init() {
            
            $firstName = new Zend_Form_Element_Text('first_name');
            $firstName->addFilter('StringTrim')
                    ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                    ->setRequired(true);
            $this->addElement($firstName);

            $lastName = new Zend_Form_Element_Text('last_name');
            $lastName->addFilter('StringTrim')
                    ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                    ->setRequired(true);
            $this->addElement($lastName);

            $workTitle = new Zend_Form_Element_Text('work_title');
            $workTitle->addFilter('StringTrim')
                    ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                    ->setRequired(false);
            $this->addElement($workTitle);

            $email = new Zend_Form_Element_Text('email');
            $email->addFilter('StringTrim')
                    ->addValidator('EmailAddress', false, array('domain' => false))
                    ->setRequired(true);
            $this->addElement($email);

            $resume = new Zend_Form_Element_Textarea('resume');
            $resume->addFilter('StringTrim')
                    ->setRequired(false);
            $this->addElement($resume);


            $memberPhoto = new Zend_Form_Element_File('member_photo');
            $memberPhoto->setRequired(false);

                $this->addElement($memberPhoto);

        }//endf init


    } //end of: class Application_Form_Admin_MemberEdit
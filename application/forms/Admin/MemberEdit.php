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
            $memberPhoto->addValidator('Count', true, 1)//ogranicavamo broj fajlova koji se mogu uploud-ovati 
                        ->addValidator('MimeType', true, array('image/jpeg', 'image/gif', 'image/png'))
                        ->addValidator('ImageSize', false, array(
                            'minwidth' => 150,
                            'minheight' => 150,
                            'maxwidth' => 2000,
                            'maxheight' => 2000
                        ))
                        ->addValidator('Size', false, array(
                            'max' => '10MB'
                        ))
                        // disable move file to destination when calling method getValues
                        ->setValueDisabled(true)
                        ->setRequired(false);

                $this->addElement($memberPhoto);

        }//endf init


    } //end of: class Application_Form_Admin_MemberEdit
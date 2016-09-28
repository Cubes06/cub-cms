<?php

    class Application_Model_MailHelper {

            public function sendMail($to_email, $from_email, $from_name, $message) {
                    $mail = new Zend_Mail('UTF-8');
                    $mail->setSubject('Poruka sa kontakt forme | Cubes cms');
                    $mail->addTo($to_email);
                    $mail->setFrom($from_email, $from_name);
                    $mail->setBodyHtml($message);
                    $mail->setBodyText($message); //alternativa ako mail klijent ne moze da prikaze html

                    return $result = $mail->send(); //zend kontaktira mail servis
            }

    } //end of class: Application_Model_MailHelper


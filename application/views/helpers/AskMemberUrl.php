<?php

    class Zend_View_Helper_AskMemberUrl extends Zend_View_Helper_Abstract {
        
       
        public function askMemberUrl($member) {
            
            return $this->view->url(
                   array(
                       'id' => $member['id'],
                       'member_slug' => $member['first_name'] . '-' . $member['last_name']
                   ), 
                   'ask-member-route', 
                   true
            );
            
        }//endf

    }//end of class: Zend_View_Helper_MemberUrl


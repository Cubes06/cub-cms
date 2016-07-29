<?php

    class Application_Model_DbTable_CmsMembers extends Zend_Db_Table_Abstract {

        const STATUS_ENABLED = 1;
        const STATUS_DISABLED = 0;

        protected $_name = 'cms_members';  //ovde ide naziv tabele

        
        /**
         * @param int $id
         * @return null|array Associative array as cms_members table columns or NULL if not found
         */
        public function getMemberById($id) {
            
            $select = $this->select();
            $select->where("id = ?", $id);

            $row = $this->fetchRow($select);

            if ($row instanceof Zend_Db_Table_Row) {
                return $row->toArray();
            }
            else {
                return null;
            }
            
        }//endf
                
        
        public function updateMember ($id, $member) {

            if (isset($member['id'])) {
                //Forbid changing of user id
                unset($member['id']);
            }
            $this->update($member, 'id = ' . $id);
            
        }//endf
        
        
        public function updateOrderOfMembers($sortedIds) {
            
            foreach ($sortedIds as $orderNumber => $id) {
                $this->update(
                        array('order_number' => $orderNumber + 1), 
                        'id = ' . $id
                );
            }
            
        }//endf
        
        
        /**
         * @param array $member  Associative array as cms_members table columns or NULL if not found
         * @return int $id od novog usera
         */
        public function insertMember($member) {
            
		//fetch order number for new member
		$select = $this->select();
		
		//Sort rows by order_number DESCENDING and fetch one row from the top
		// with biggest order_number
		$select->order('order_number DESC');
		
		$memberWithBiggestOrderNumber = $this->fetchRow($select);
		
		if ($memberWithBiggestOrderNumber instanceof Zend_Db_Table_Row) {
			
			$member['order_number'] = $memberWithBiggestOrderNumber['order_number'] + 1;
		} 
                else {
			// table was empty, we are inserting first member
			$member['order_number'] = 1;
		}
		
		$id = $this->insert($member);
		
		return $id;
	}//endf
        
        
        /**
         * @param int $id ID of member to delete
         */
        public function deleteMember($id) {
		
            $memberPhotoFilePath = PUBLIC_PATH . '/uploads/members/' . $id . '.jpg';
            
            if (is_file($memberPhotoFilePath)) {
                //delete member photo file
                unlink($memberPhotoFilePath);
            }
            
            //member who is going to be deleted
            $member = $this->getMemberById($id);
            
            //this updates order_numbers of all members whose order_numbers are greater than the current order_numer
            $this->update(
                    array('order_number' => new Zend_Db_Expr('order_number - 1')),
                    'order_number > ' . $member['order_number']
            );

            $this->delete('id = ' . $id);
	
        }//endf
        
        
        
        /**
         * @param int $id    ID of member to enable
         */
        public function enableMember($id) {
            $this->update(
                    array('status' => self::STATUS_ENABLED), 
                    'id = ' . $id
            );
        }//endf
        
        
        /**
         * @param int $id    ID of member to disable
         */
        public function disableMember($id) {
            $this->update(
                    array('status' => self::STATUS_DISABLED), 
                    'id = ' . $id
            );
        }//endf
              
        
    } //end of: class Application_Model_DbTable_CmsMembers

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
              
        
        public function getActiveMembers() {
            $select = $this->select();
            
            $select->from('cms_members', array("num" => "COUNT(*)"))
                   ->where('status = ?', self::STATUS_ENABLED);

            $row = $this->fetchRow($select);

            if ($row instanceof Zend_Db_Table_Row) {
                return $row["num"];
            }
            else {
                return 0;
            }
        }
        
        
        public function getTotalMembers() {
            $select = $this->select();
            
            $select->from('cms_members', array("num" => "COUNT(*)"));

            $row = $this->fetchRow($select);

            if ($row instanceof Zend_Db_Table_Row) {
                return $row["num"];
            }
            else {
                return 0;
            }
            
        }
        
        
        
        /**
        * Array $parameters is keeping search parameters.
        * Array $parameters must be in following format:
        * 		array(
        * 			'filters' => array(
        * 				'status' => 1,
        * 				'id' => array(3, 8, 11)
        * 			),
        * 			'orders' => array(
        * 				'username' => 'ASC', // key is column , if value is ASC then ORDER BY ASC,
        * 				'first_name' => 'DESC', // key is column, if value is DESC then ORDER BY DESC
        * 			),
        * 			'limit' => 50, //limit result set to 50 rows
        * 			'page' => 3 // start from page 3. If no limit is set, page is ignored
        * 		)
        * @param array $parameters Asoc array with keys "filters", "orders", "limit" and "page".
        */
        public function search(array $parameters = array()) {

            $select = $this->select();

            if (isset($parameters['filters'])) {
                $filters = $parameters['filters'];
                $this->processFilters($filters, $select);
            }

            if (isset($parameters['orders'])) {
                $orders = $parameters['orders'];

                foreach ($orders as $field => $orderDirection) {
                    switch ($field) {
                        case 'id':
                        case 'first_name':
                        case 'last_name':
                        case 'email':
                        case 'status':
                        case 'order_number':
                            if ($orderDirection === 'DESC') {
                                $select->order($field . ' DESC');
                            } else {
                                $select->order($field);
                            }
                            break;
                    }
                }
            }//endif

            if (isset($parameters['limit'])) {
                if (isset($parameters['page'])) {
                    // page is set do limit by page
                    $select->limitPage($parameters['page'], $parameters['limit']);
                } 
                else {
                    // page is not set, just do regular limit
                    $select->limit($parameters['limit']);
                }
            }

            //die($select->assemble());

            return $this->fetchAll($select)->toArray();
            
        }//endf

        
        /**
         * @param array $filters See function search $parameters['filters']
         * @return int Count of rows that match $filters
         */
        public function count(array $filters = array()) {

            $select = $this->select();

            $this->processFilters($filters, $select);

            // reset previously set columns for resultset
            $select->reset('columns');
            // set one column/field to fetch and it is COUNT function
            $select->from($this->_name, 'COUNT(*) as total');

            $row = $this->fetchRow($select);

            return $row['total'];
        }//endf
        

        /**
         * Fill $select object with WHERE conditions
         * @param array $filters
         * @param Zend_Db_Select $select
         */
        protected function processFilters(array $filters, Zend_Db_Select $select) {

            // $select object will be modified outside this function
            // object are always passed by reference

            foreach ($filters as $field => $value) {

                switch ($field) {
                    case 'id':
                    case 'first_name':
                    case 'last_name':
                    case 'email':
                    case 'status':
                    case 'work_title':
                        if (is_array($value)) {
                            $select->where($field . ' IN (?)', $value);
                        } else {
                            $select->where($field . ' = ?', $value);
                        }
                        break;
                    
                    case 'first_name_search':
                        $select->where('first_name LIKE ?', '%' . $value . '%');
                        break;
                    
                    case 'last_name_search':
                        $select->where('last_name LIKE ?', '%' . $value . '%');
                        break;
                    
                    case 'email_search':
                        $select->where('email LIKE ?', '%' . $value . '%');
                        break;

                    case 'work_title_search':
                        $select->where('work_title LIKE ?', '%' . $value . '%');
                        break;
                    
                    case 'id_exclude':
                        if (is_array($value)) {
                            $select->where('id NOT IN (?)', $value);
                        } 
                        else {
                            $select->where('id != ?', $value);
                        }
                        break;
                        
                   
                }//endswitch
            }//endforeach
        }//endf

        
        
    } //end of: class Application_Model_DbTable_CmsMembers

<?php

    class Application_Model_DbTable_CmsServices extends Zend_Db_Table_Abstract {
        
        const STATUS_ENABLED = 1; 
        const STATUS_DISABLED = 0;

        protected $_name = 'cms_services';
 
        
        /**
         * @param int $id
         * @return null|array   Associative array as cms_services table columns or NULL if not found
         */
        public function getServiceById($id) {
            
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
        

        /**
         * @param int $id
         * @param array $service   Associative array with keys as column names and values as column new values
         */
        public function updateService($service, $id) {
            if (isset($service['id'])) {
                //Forbid changing of user id
                unset($service['id']);
            }
            $this->update($service, 'id = ' . $id);
        }//endf
        

        public function insertService($service) {
            
            $select = $this->select();
            
            $select->from($this, array(new Zend_Db_Expr('(MAX(order_number))+1 AS order_number')));
            
            $row = $this->fetchRow($select);
            
            $service['order_number'] = $row['order_number'];

            $this->insert($service); 
        }//endf
        
        
        public function updateOrderOfServices($sortedIds) {
            
            foreach ($sortedIds as $orderNumber => $id) {
                $this->update(
                        array('order_number' => $orderNumber + 1),  
                        'id = ' . $id
                );
            }
            
        }//endf
        
        
        /**
         * @param int $id ID of service to delete
         */
        public function deleteService($id) {
            $this->delete('id = ' . $id);
        }
        
        
        /**
         * @param int $id    ID of service to disable
         */
        public function disableService($id) {
            $this->update(array(
                'status' => self::STATUS_DISABLED
            ), 'id = ' . $id);
        }
        
        
        /**
         * @param int $id    ID of service to enable
         */
        public function enableService($id) {
            $this->update(
                    array('status' => self::STATUS_ENABLED), 
                    'id = ' . $id
            );
        }
        
        
        public function getActiveServices() {
            $select = $this->select();
            
            $select->from('cms_services', array("num" => "COUNT(*)"))
                   ->where('status = ?', self::STATUS_ENABLED);

            $row = $this->fetchRow($select);

            if ($row instanceof Zend_Db_Table_Row) {
                return $row["num"];
            }
            else {
                return 0;
            }
        }
        
        
        public function getTotalServices() {
            $select = $this->select();
            
            $select->from('cms_services', array("num" => "COUNT(*)"));

            $row = $this->fetchRow($select);

            if ($row instanceof Zend_Db_Table_Row) {
                return $row["num"];
            }
            else {
                return 0;
            }
            
        }
        
        
    } //end of: class Application_Model_DbTable_CmsServices
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
                        case 'title':
                        case 'description':
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
                } else {
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
                    case 'title':
                    case 'description':
                    case 'status':
                        if (is_array($value)) {
                            $select->where($field . ' IN (?)', $value);
                        } else {
                            $select->where($field . ' = ?', $value);
                        }
                        break;

                    case 'title_search':
                        $select->where('title LIKE ?', '%' . $value . '%');
                        break;

                    case 'description_search':
                        $select->where('description LIKE ?', '%' . $value . '%');
                        break;

                    case 'id_exclude':
                        if (is_array($value)) {
                            $select->where('id NOT IN (?)', $value);
                        } else {
                            $select->where('id != ?', $value);
                        }
                        break;

                }//endswitch
            }//endforeach
        }//endf



    } //end of: class Application_Model_DbTable_CmsServices
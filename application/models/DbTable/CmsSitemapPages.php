<?php

    class Application_Model_DbTable_CmsSitemapPages extends Zend_Db_Table_Abstract {
        
        const STATUS_ENABLED = 1;
        const STATUS_DISABLED = 0;
        
        protected $_name = 'cms_sitemap_pages';
        protected static $sitemapPagesMap; //staticki clanovi su dostupni samo u statickim funkcijama
        
        /**
         * 
         * @return array With keys as sitemap page ids and values as assoc. array with keys url and type
         */
        public static function getSitemapPagesMap() {
            //lazy loading -> samo prvim pozivom dovlacimo neke podatke
            if (!self::$sitemapPagesMap) {
                $sitemapPagesMap = array();
            
                $cmsSitemapPagesDbTable = new self(); //same as: new Application_Model_DbTable_CmsSitemapPages

                $sitemapPages = $cmsSitemapPagesDbTable->search(array(
                    'orders' => array(
                        'parent_id' => 'ASC',
                        'order_number' => 'ASC'
                    )
                ));

                foreach ($sitemapPages as $sitemapPage) {
                    $type = $sitemapPage['type'];
                    $url = $sitemapPage['url_slug'];

                    if (isset($sitemapPagesMap[$sitemapPage['parent_id']])) {
                        $url = $sitemapPagesMap[$sitemapPage['parent_id']]['url'] . '/' .$url;
                    }
                    
                    $sitemapPagesMap[$sitemapPage['id']] = array(
                        'url' => $url,
                        'type' => $type
                    );
                }

                

                self::$sitemapPagesMap = $sitemapPagesMap;
            }
            
            return self::$sitemapPagesMap;
            
        }
        
        
        
        
        /**
         * @param int $id
         * @return null|array Associative array as cms_sitemapPages table columns or NULL if not found
         */
        public function getSitemapPageById($id) {
            
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
                
        
        public function updateSitemapPage ($id, $sitemapPage) {

            if (isset($sitemapPage['id'])) {
                //Forbid changing of user id
                unset($sitemapPage['id']);
            }
            $this->update($sitemapPage, 'id = ' . $id);
            
        }//endf
        
        
        public function updateOrderOfSitemapPages($sortedIds) {
            
            foreach ($sortedIds as $orderNumber => $id) {
                $this->update(
                        array('order_number' => $orderNumber + 1), 
                        'id = ' . $id 
                );
            }
            
        }//endf
        
        
        /**
         * @param array $sitemapPage  Associative array as cms_sitemapPages table columns or NULL if not found
         * @return int $id od novog usera
         */
        public function insertSitemapPage($sitemapPage) {
            
		//fetch order number for new sitemapPage
		$select = $this->select();
		
		//Sort rows by order_number DESCENDING and fetch one row from the top
		// with biggest order_number
		$select->where('parent_id = ?', $sitemapPage['parent_id'])
                        ->order('order_number DESC');
		
		$sitemapPageWithBiggestOrderNumber = $this->fetchRow($select);
		
		if ($sitemapPageWithBiggestOrderNumber instanceof Zend_Db_Table_Row) {
			
			$sitemapPage['order_number'] = $sitemapPageWithBiggestOrderNumber['order_number'] + 1;
		} 
                else {
			// table was empty, we are inserting first sitemapPage
			$sitemapPage['order_number'] = 1;
		}
		
		$id = $this->insert($sitemapPage);
		
		return $id;
	}//endf
        
        
        /**
         * @param int $id ID of sitemapPage to delete
         */
        public function deleteSitemapPage($id) {
	                      
            //sitemapPage who is going to be deleted
            $sitemapPage = $this->getSitemapPageById($id);
            
            $children = $this->search(array(
                'filters' => array(
                    'parent_id' => $id
                )
            ));
            
            if (count($children) > 0) {
                //delete children pages recursively
                foreach ($children as $child) {
                    $this->deleteSitemapPage($child['id']);
                }
            }
            
            
            //this updates order_numbers of all sitemapPages whose order_numbers are greater than the current order_numer
            $this->update(
                    array('order_number' => new Zend_Db_Expr('order_number - 1')),
                    'order_number > ' . $sitemapPage['order_number'] . ' AND parent_id = ' . $sitemapPage['parent_id']
                    
            );

            $this->delete('id = ' . $id);
	
        }//endf
        
        
        /**
         * @param int $id    ID of sitemapPage to enable
         */
        public function enableSitemapPage($id) {
            $this->update(
                    array('status' => self::STATUS_ENABLED), 
                    'id = ' . $id
            );
        }//endf
        
        
        /**
         * @param int $id    ID of sitemapPage to disable
         */
        public function disableSitemapPage($id) {
            $this->update(
                    array('status' => self::STATUS_DISABLED), 
                    'id = ' . $id
            );
        }//endf
              
        
        public function getActiveSitemapPages() {
            $select = $this->select();
            
            $select->from('cms_sitemapPages', array("num" => "COUNT(*)"))
                   ->where('status = ?', self::STATUS_ENABLED);

            $row = $this->fetchRow($select);

            if ($row instanceof Zend_Db_Table_Row) {
                return $row["num"];
            }
            else {
                return 0;
            }
        }
        
        
        public function getTotalSitemapPages() {
            $select = $this->select();
            
            $select->from('cms_sitemapPages', array("num" => "COUNT(*)"));

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
                        case 'short_title':
                        case 'url_slug':
                        case 'title':
                        case 'parent_id':
                        case 'type':
                        case 'order_number':
                        case 'status':
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
                        case 'short_title':
                        case 'url_slug':
                        case 'title':
                        case 'parent_id':
                        case 'type':
                        case 'order_number':
                        case 'status':
                        if (is_array($value)) {
                            $select->where($field . ' IN (?)', $value);
                        } else {
                            $select->where($field . ' = ?', $value);
                        }
                        break;
                    
                    case 'body_search':
                        $select->where('body LIKE ?', '%' . $value . '%');
                        break;
                    case 'short_title_search':
                        $select->where('short_title LIKE ?', '%' . $value . '%');
                        break;
                    
                    case 'url_slug_search':
                        $select->where('url_slug LIKE ?', '%' . $value . '%');
                        break;
                    
                    case 'description_search':
                        $select->where('description LIKE ?', '%' . $value . '%');
                        break;
                    
                    case 'title_search':
                        $select->where('title LIKE ?', '%' . $value . '%');
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

        /**
         * @param int $id The ID of sitemap page
         * return array Sitemap page rows in path
         */
        public function getSitemapPageBreadcrumbs($id) {
            
            $sitemapPagesBreadcrumbs = array();
            
            
            //PRVA VERZIJA
            while($sitemapPageInPath = $this->getSitemapPageById($id)) {
                array_unshift($sitemapPagesBreadcrumbs, $sitemapPageInPath);
                $id = $sitemapPageInPath['parent_id'];
            }
            
            
            //DRUGA VERZIJA
//            $parentId = $id;
//            
//            while($id > 0) {
//                $sitemapPageInPath = $this->getSitemapPageById($id);
//                
//                if ($sitemapPageInPath) {
//                    $id = $sitemapPageInPath['parent_id'];
//                    
//                    //add current page at the beggining of breadcrumbs array
//                    array_unshift($sitemapPagesBreadcrumbs, $sitemapPageInPath);
//                }
//                else {
//                    $id = 0;
//                }
//            }
            
            
            return $sitemapPagesBreadcrumbs;
        }
        
        /**
         * Returns count by type, examples:
         * array (
         *  'StaticPage' => 3,
         *  'AboutPage' =>  1',
         *  'ContactPage' => 1,
         *  ...
         * @param type $filters
         * @return array Count by type
         */
        public function countByTypes($filters = array()) {
            
            $select = $this->select();

            $this->processFilters($filters, $select);

            // reset previously set columns for resultset
            $select->reset('columns');
            // set one column/field to fetch and it is COUNT function
            $select->from($this->_name, array(
                'type',
                'COUNT(*) as total_by_type'
            ));
            $select->group('type');
            
            $rows = $this->fetchAll($select);
            
            $countByTypes = array();
            
            foreach ($rows as $row) {
                $countByTypes[$row['type']] = $row['total_by_type'];
            }
            
            return $countByTypes;
            
        }
        
        
        
    } //end of class: Application_Model_DbTable_CmsSitemapPages
    




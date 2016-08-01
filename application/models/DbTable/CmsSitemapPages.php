<?php

    class Application_Model_DbTable_CmsSitemapPages extends Zend_Db_Table_Abstract {
        
        const STATUS_ENABLED = 1;
        const STATUS_DISABLED = 0;
        
        protected $_name = 'cms_sitemap_pages';
        
        
        
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
        
        
        
    } //end of class: Application_Model_DbTable_CmsSitemapPages
    




<?php

    class Application_Model_DbTable_CmsCitaci extends Zend_Db_Table_Abstract {
        
        public function getCitacById($id) {
            $select = $this->select();
            $select->where("id = ?", $id);

            $row = $this->fetchRow($select);

            if ($row instanceof Zend_Db_Table_Row) {
                return $row->toArray();
            }
            else {
                return null;
            }
        }
      
    }
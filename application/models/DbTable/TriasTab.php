<?php

class Application_Model_DbTable_TriasTab extends Zend_Db_Table_Abstract
{

    protected $_name = 'Invoice';
    

    //getting all the the rows out of Invoice Table
    public function	getInvoice($id)
    {
    	$id = (int)$id;
    	$row = $this->fetchRow('id = ' . $id);
    	if (!$row) {
    		throw new Exception("Could not find a row $id");
    	}
    	return $row->toArray();
    }
    
}


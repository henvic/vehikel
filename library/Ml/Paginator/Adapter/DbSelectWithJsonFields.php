<?php

/**
 * @see Zend_Paginator_Adapter_Interface
 */

/**
 * @category   Zend
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Ml_Paginator_Adapter_DbSelectWithJsonFields extends Zend_Paginator_Adapter_DbSelect
{
    protected $_jsonFields = array();

    /**
     * Constructor.
     *
     * @param Zend_Db_Select $select The select query
     * @param array $jsonFields array of fields to be decoded as a JSON object
     */
    public function __construct(Zend_Db_Select $select, $jsonFields)
    {
        $this->_jsonFields = $jsonFields;

        parent::__construct($select);
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->_select->limit($itemCountPerPage, $offset);

        return $this->decodeJsonDataInPageContents($this->_select->query()->fetchAll());
    }

    protected function decodeJsonDataInPageContents($items)
    {
        foreach ($items as $itemKey => $item) {
            foreach ($this->_jsonFields as $jsonField) {
                $items[$itemKey][$jsonField] = json_decode($item[$jsonField], true);
            }
        }

        return $items;
    }
}

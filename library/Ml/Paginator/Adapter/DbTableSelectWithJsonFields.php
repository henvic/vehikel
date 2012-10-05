<?php

/**
 * @see Ml_Paginator_Adapter_DbSelectDataTransform
 */

class Ml_Paginator_Adapter_DbTableSelectWithJsonFields extends Ml_Paginator_Adapter_DbSelectWithJsonFields
{
    /**
     * Returns a array of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->_select->limit($itemCountPerPage, $offset);

        $itemsObject = $this->_select->getTable()->fetchAll($this->_select);

        $items = $itemsObject->toArray();

        return $this->decodeJsonDataInPageContents($items);
    }
}
<?php

trait Ml_Model_Db_Table_History
{
    protected function saveHistorySnapshot($id)
    {
        //the history is built using a best effort, non-transational way
        $historySql = "INSERT INTO "
            . $this->_dbAdapter->quoteTableAs($this->_dbHistoryTableName)
            . "SELECT UUID() as history_id, "
            . $this->_dbAdapter->quoteTableAs($this->_dbTableName)
            . ".*, CURRENT_TIMESTAMP as change_time FROM "
            . $this->_dbAdapter->quoteTableAs($this->_dbTableName)
            . "WHERE id = :id";

        $this->_dbAdapter->query($historySql, array("id" => $id));
    }
}

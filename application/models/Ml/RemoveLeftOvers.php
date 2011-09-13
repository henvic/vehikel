<?php
//This is to schedule to remove data left over by users
//who had their accounts deleted from the system
class Ml_Model_RemoveLeftOvers extends Ml_Model_Db_Table
{
    protected $_name = "remove_deleted_user_leftovers";
}

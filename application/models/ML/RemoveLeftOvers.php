<?php
//This is to schedule to remove data left over by users
//who had their accounts deleted from the system
class ML_RemoveLeftOvers extends ML_Db
{
    protected $_name = "remove_deleted_user_leftovers";
}

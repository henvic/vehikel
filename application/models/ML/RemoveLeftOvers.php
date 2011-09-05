<?php
//This is to schedule to remove data left over by users who were deleted from the system
class ML_RemoveLeftOvers extends ML_getModel
{    
    protected $_name = "remove_deleted_user_leftovers";
}

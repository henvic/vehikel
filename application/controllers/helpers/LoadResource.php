<?php
/**
 * This helper action loads the most necessary data
 * for the resources of the website
 * 
 * @author henrique
 *
 */
class Zend_Controller_Action_Helper_LoadResource extends Zend_Controller_Action_Helper_Abstract
{
    public function pseudoshareSetUp ()
    {
        $registry = Zend_Registry::getInstance();
        
        $request = $this->getRequest();
        
        if ($request->getUserParam('username') &&
         ! $registry->isRegistered("userInfo")) {
            //avoid calling the DB again for nothing
            if (isset($registry['signedUserInfo']) &&
            $registry['signedUserInfo']['alias'] == $request->getUserParam('username')) {
                $userInfo = $registry['signedUserInfo'];
            } else {
                $people = ML_People::getInstance();
                
                $userInfo = $people
                 ->getByUsername($request->getUserParam('username'));
            }
            if (!$userInfo) {
                $registry->set("notfound", true);
                throw new Exception("User does not exists.");
            }
            $registry->set("userInfo", $userInfo);
            $registry->set("requestUserParams", 
            $this->getRequest()
                ->getUserParams());
            if ($this->getRequest()->getUserParam("share_id")) {
                $share = ML_Share::getInstance();
                $shareInfo = $share->getById($this->getRequest()
                    ->getUserParam("share_id"));
                if (!$shareInfo) {
                    $registry->set("notfound", true);
                    throw new Exception("Share does not exists.");
                } else if ($shareInfo['byUid'] != $userInfo['id']) {
                        $registry->set("notfound", true);
                        throw new Exception("Share owned by another user.");
                }
                $registry->set("shareInfo", $shareInfo);
            }
        }
    }
}

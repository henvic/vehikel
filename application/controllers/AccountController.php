<?php

class AccountController extends Zend_Controller_Action
{
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
    }
    
    public function indexAction()
    {
        $request = $this->getRequest();
        
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get("config");
        
        $people = Ml_Model_People::getInstance();
        
        $profile = new Ml_Model_Profile();
        
        $account = new Ml_Model_Account();
        
        $form = $account->settingsForm();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $profileInfo = $profile->getById($signedUserInfo['id']);
        
        //only data that can be changed can be here
        $listOfData = array(
            "name" => $signedUserInfo['name'],
            "email" => $signedUserInfo['email'],
            "private_email" => $signedUserInfo['private_email'],
            "about" => $profileInfo['about'],
            "website" => $profileInfo['website'],
            "location" => $profileInfo['location'],
        );
        
        $form->setDefaults($listOfData);
        
        if ($request->isPost()) {
            $form->isValid($request->getPost());
            $errors = $form->getErrors();
            
            $changeData = Array();
            
            $rec = $form->getValues();
            
            //update
            foreach ($listOfData as $key => $value) {
                if (empty($errors[$key]) && $rec[$key] != $value) {
                    $changeData[$key] = $rec[$key];
                }
            }
            
            if (!empty($changeData)) {
                $changeDataLessEmail = $changeData;
                
                if (isset($changeData['email'])) {
                    unset($changeDataLessEmail['email']);
                }
                
                if (!empty($changeDataLessEmail)) {
                    //just a small state protection
                    if (isset($changeDataLessEmail['private_email'])) {
                        $changeDataLessEmail['private_email'] = 1;
                    }
                    
                    $profileFields = array("website", "location", "about");
                    
                    $changeProfileData = array();
                    
                    foreach ($profileFields as $field) {
                        if (isset($changeDataLessEmail[$field])) {
                            $changeProfileData[$field] = $changeDataLessEmail[$field];
                            unset($changeDataLessEmail[$field]);
                        }
                    }
                    
                    if (! empty($changeDataLessEmail)) {
                        $people->update($changeDataLessEmail, 
                        $people->getAdapter()
                            ->quoteInto("id = ?", $signedUserInfo['id']));
                    }
                    
                    if (! empty($changeProfileData)) {
                        if (isset($changeProfileData['about'])) {
                            $purifier = Ml_Model_HtmlPurifier::getInstance();
                            
                            $changeProfileData['about_filtered'] =
                            $purifier->purify($changeProfileData['about']);
                        }
                        
                        $profile->update($changeProfileData,
                         $people->getAdapter()->quoteInto("id = ?", $signedUserInfo['id']));
                    }
                    
                    $signedUserInfo = array_merge($signedUserInfo, $changeDataLessEmail);
                    
                    $registry->set("signedUserInfo", $signedUserInfo);
                }
                
                if (isset($changeData['about']) && sizeof($changeData) == 1) {
                    $redirectToProfile = true;
                }
            }
            
            if (isset($changeData['email'])) {
                //changeEmail table
                $emailChange = new Ml_Model_EmailChange();
                
                $securitycode =
                $emailChange->askNew($signedUserInfo['id'], $changeData['email'], $signedUserInfo['name']);
                
                $mail = new Zend_Mail();
                
                $this->view->securitycode = $securitycode;
                
                $mail->setBodyText($this->view->render("account/emailChange.phtml"))
                ->setFrom($config['robotEmail']['addr'], $config['robotEmail']['name'])
                ->addTo($changeData['email'], $signedUserInfo['name'])
                ->setSubject('Changing your '.$config['applicationname'].' email')
                ->send();
                
                $this->view->changeEmail = true;
            } else if (isset($redirectToProfile)) {
                $this->_redirect($router
                 ->assemble(array("username" => $signedUserInfo['alias']),
                  "profile")."?about_check=true", array("exit"));
            }
        }
        
        $this->view->accountForm = $form;
    }
    

    public function twitterAction()
    {
        require_once EXTERNAL_LIBRARY_PATH . '/twitter-async/EpiCurl.php';
        require_once EXTERNAL_LIBRARY_PATH . '/twitter-async/EpiOAuth.php';
        require_once EXTERNAL_LIBRARY_PATH . '/twitter-async/EpiTwitter.php';
        
        $auth = Zend_Auth::getInstance();
        
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get('config');
        
        $request = $this->getRequest();
        
        $twitterConf = $config['services']['twitter'];
        
        $params = $request->getParams();
        
        $twitter = Ml_Model_Twitter::getInstance();
        
        $twitterObj = new EpiTwitter($twitterConf['key'], $twitterConf['secret']);
        
        $removeTwitterForm = $twitter->removeForm();
        $addTwitterForm = $twitter->addForm();
        
        if ($request->isPost()) {
            if ($removeTwitterForm->isValid($request->getPost())) {
                $twitter->delete($twitter->getAdapter()->quoteInto('uid = ?', $auth->getIdentity()));
            } else if ($addTwitterForm->isValid($request->getPost())) {
                $twitterAuthenticateUrl = $twitterObj->getAuthenticateUrl();
                if (Zend_Uri::check($twitterAuthenticateUrl)) {
                    $this->_redirect($twitterAuthenticateUrl, array("exit"));
                }
            }
        }
        
        $twitterInfo = $twitter->getSignedUserTwitterAccount();
        
        if (isset($params['oauth_token']) && !is_array($twitterInfo)) {
            try {
                    $twitterObj->setToken($params['oauth_token']);
                    $token = $twitterObj->getAccessToken();
                    
                    $tokenArray = array('oauth_token' => $token->oauth_token,
                    'oauth_token_secret' => $token->oauth_token_secret);
                    
                    $twitter->setTwitterAccount($tokenArray);
                    
                    $this->_redirect($router->assemble(array(), "accounttwitter"), array("exit"));
            } catch (Exception $e) {
                $this->view->twitterApiError = true;
            }
        }
        if (! is_array($twitterInfo)) {
            $this->view->getTwitterOauth = true;
        } else {
            //how old is the data retrieved from Twitter?
            $lastCheckDate  = new Zend_Date($twitterInfo['timestamp'], Zend_Date::ISO_8601);
            if ($lastCheckDate->getTimestamp() < time() - 86400) {
                try {
                    $twitter->setTwitterAccount(false, $twitterInfo);
                    $twitterInfo = $twitter->getSignedUserTwitterAccount();
                } catch(Exception $e)
                {
                    $this->view->invalidTwitterAccount = true;
                }
            }
            $this->view->twitterInfo = $twitterInfo;
        }
        $this->view->addTwitterForm = $addTwitterForm;
        $this->view->removeTwitterForm = $removeTwitterForm;
    }
    
    public function pictureAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $request = $this->getRequest();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $picture = new Ml_Model_PictureUpload();
        $people = Ml_Model_People::getInstance();
        
        $form = $picture->pictureForm();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            if ($form->getValue("delete")) {
                $change = $picture->deleteAvatar($signedUserInfo);
            } else if ($form->Image->isUploaded()) {
                $fileInfo = $form->Image->getFileInfo();
                
                $change = $picture->setAvatar($signedUserInfo, $fileInfo['Image']['tmp_name']);
            }
            
            if (isset($change) && $change) {
                //refresh
                $signedUserInfo = $people->getById($signedUserInfo['id']);
                $registry->set("signedUserInfo", $signedUserInfo);
            }
            
            $form->getValues();
        }
        
        $this->view->submitPictureForm = $form;
    }
    
    public function applicationsAction()
    {
        $auth = Zend_Auth::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        $params = $request->getParams();
        
        $this->_helper->loadOauthstore->setinstance();
        
        $store = OAuthStore::instance();
        
        if (isset($params['revoke_token'])) {
            $store->deleteConsumerAccessToken($params['revoke_token'], $auth->getIdentity());
            $this->_redirect($router->assemble(array(), "accountapps"), array("exit"));
        }
        
        $listConsumer = $store->listConsumerTokens($auth->getIdentity());
        
        $this->view->listConsumerTokens = $listConsumer;
    }
}

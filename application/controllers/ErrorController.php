<?php

/**
 * Error controller
 * The error action is called when a error happens (such as page not found)
 * @author Henrique Vicente <henriquevicente@gmail.com>
 *
 * @todo ajax response error handling
 */
class ErrorController extends Ml_Controller_Action
{
    public function notFoundAction()
    {
        return $this->_forward("error");
    }

    public function errorAction()
    {
        $params = $this->_request->getParams();

        // Ensure the default view suffix is used
        // so we always return the error in the right view type
        $this->_helper->viewRenderer->setViewSuffix('phtml');

        // If it is a Ajax rquest, let's just send the headers and no content at all
        if($this->_request->isXmlHttpRequest()) {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
        }

        $errors = $this->_getParam('error_handler');

        // check if the system failed due to a exception or a user error / broken link
        if (! $this->_getParam('error_handler')) {
            $this->getResponse()->setHttpResponseCode(404);
            $this->view->statusCode = 404;
            $this->view->params = $this->_request->getUserParams();
            return;
        } else {
            // pass the actual exception object to the view
            $this->view->exception = $errors->exception;
            $this->getResponse()->setHttpResponseCode(500);
            $this->view->statusCode = 500;
            $this->view->params = $errors->request->getUserParams();
        }
    }
}

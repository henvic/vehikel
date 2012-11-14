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
            if ($this->getResponse()->getHttpResponseCode() != 200) {
                $responseCode = $this->getResponse()->getHttpResponseCode();
            } else {
                $responseCode = 404;
            }

            $this->getResponse()->setHttpResponseCode($responseCode);
            $this->view->statusCode = $responseCode;
            $this->view->params = $this->_request->getUserParams();
            return;
        } else {
            // pass the actual exception object to the view or send it back if ajax
            $this->view->exception = $errors->exception;
            $this->getResponse()->setHttpResponseCode(500);
            $this->view->statusCode = 500;
            $this->view->params = $errors->request->getUserParams();

            if($this->_request->isXmlHttpRequest()) {
                $errorInfo = ["error" => "application-error"];

                if ("development" == APPLICATION_ENV) {
                    $errorInfo["debug"] = [
                        "params" => $errors->request->getUserParams(),
                        "message" => $errors->exception->getMessage(),
                        "trace" =>
                        str_replace(array(": ", "#"), array(":<br />   ", "<br />#"),
                            $errors->exception->getTraceAsString()
                        )
                    ];
                }
                $this->_helper->json($errorInfo);
                return;
            }
        }
    }
}

<?php

/**
 * SDR Ajax ViewController
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'SDR.php');

class AjaxSDR extends SDR
{
    public function process()
    {
        try {
            parent::process();

            if($this->context->shouldJson()) {
                $content = json_encode($this->context->getContent());
            } else {
                $content = $this->context->getContent();
            }
        } catch(PermissionException $pe) {
            $error = new JsonError('401 Unauthorized');
            $error->setMessage('You are not authorized to perform this action.  You may need to sign back in.');
            $error->renderStatus();
            $content = $error->encode();
        } catch(Exception $e) {
            $error = new JsonError('500 Internal Server Error');
            $error->setMessage($e->getMessage());

            $error->setExceptionId(
                \sdr\Environment::getInstance()->handleException($e, 'Uncaught Exception from REST API'));
            $error->renderStatus();
            $content = $error->encode();
        }

        $callback = $this->context->get('callback');

        $response = !is_null($callback)
            ? "$callback($content)"
            : $content;

        header('Content-Type: application/json; charset=utf-8');

        echo $response;

        \sdr\Environment::getInstance()->cleanExit();
    }
}

?>

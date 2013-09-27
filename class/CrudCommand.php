<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

abstract class CrudCommand extends Command
{
    function execute(CommandContext $context)
    {
        $rh = getallheaders();
        header('Allow: GET,HEAD,POST,PUT,DELETE,OPTIONS');

        if(array_key_exists('Origin', $rh)) {
            header('Access-Control-Allow-Origin:'.$rh['Origin']);
        }

        if(array_key_exists('Access-Control-Request-Headers', $rh)) {
            header('Access-Control-Allow-Headers:'.$rh['Access-Control-Request-Headers']);
        }

        header('Access-Control-Allow-Credentials: true');

        switch($context->getMethod()) {
            case 'GET':
                return $this->get($context);
            case 'POST':
                return $this->post($context);
            case 'PUT':
                return $this->put($context);
            case 'DELETE':
                return $this->delete($context);
            case 'OPTIONS':
                return $this->options($context);
            default:
                PHPWS_Core::initModClass('sdr', 'exception/HttpException.php');
                throw new HttpException($context->getMethod(), 405);
        }
    }

    public function options(CommandContext $context)
    {
        $this->get($context);
    }

    public function get(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'exception/HttpException.php');
        throw new HttpException($context->getMethod(), 405);
    }

    public function post(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'exception/HttpException.php');
        throw new HttpException($context->getMethod(), 405);
    }

    public function put(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'exception/HttpException.php');
        throw new HttpException($context->getMethod(), 405);
    }

    public function delete(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'exception/HttpException.php');
        throw new HttpException($context->getMethod(), 405);
    }
}

?>

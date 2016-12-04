<?php
/**
 * Created by PhpStorm.
 * User: bugra
 * Date: 27.11.2016
 * Time: 19:14
 */

namespace Framework\Util\Mailer;


class phpmailerException extends \Exception
{
    public function errorMessage() {
        $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
        return $errorMsg;
    }
}
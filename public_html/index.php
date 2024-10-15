<?php
/**
 * @author    Phil E. Taylor <phil@phil-taylor.com>
 * @copyright 2024 Red Evolution Limited.
 * @license   GPL
 */

namespace RedEvo;

use Exception;

require_once '../vendor/autoload.php';

class IncomingRequestHandler
{
    public function __construct(LogHander $logger)
    {
        if ($_SERVER && array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $logger->debug('IncomingRequestHandler invoked by '.$_SERVER['REMOTE_ADDR']);
        } else {
            $logger->debug('IncomingRequestHandler invoked');
        }

        try {
            if (!is_array($_SERVER)
                || !array_key_exists('REQUEST_METHOD', $_SERVER)
                || $_SERVER["REQUEST_METHOD"] !== "POST") {
                throw new Exception("Invalid method.", 418);
            }

            if (!is_array($_FILES) || !array_key_exists('file', $_FILES)) {
                throw new Exception("Invalid request.", 418);
            }

            $file = $_FILES['file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Error uploading file.", 400);
            }

            $file_name = date('Y-m-d-H-i-s').'-'.$file['name'];

            move_uploaded_file($file['tmp_name'], '../data/'.$file_name);

            $logger->info('File received successfully: '.$file_name);

            echo "File received successfully.";
        } catch (Exception $e) {
            $logger->critical(sprintf('Exception %s %s', $e->getCode(), $e->getMessage()));
            http_response_code($e->getCode());
            echo $e->getMessage();
        }
    }
}


$obj = new IncomingRequestHandler(new LogHander());

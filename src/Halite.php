<?php
/**
 * @author    Phil E. Taylor <phil@phil-taylor.com>
 * @copyright 2024 Red Evolution Limited.
 * @license   GPL
 */

namespace RedEvo;

use ParagonIE\Halite\File;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\KeyFactory;
use Symfony\Component\Dotenv\Dotenv;

class Halite
{

    private EncryptionKey $encryptionKey;

    public function __construct()
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../.env');

        $passwd = new HiddenString($_ENV['ENCRYPTION_PHRASE']);
        $salt = hex2bin($_ENV['ENCRYPTION_SALT']);

        $this->encryptionKey = KeyFactory::deriveEncryptionKey($passwd, $salt);
    }

    public function encrypt(string $file)
    {
        if (File::encrypt($file, $file.'.enc', $this->encryptionKey)) {
            unlink($file);
        }
    }

    public function decrypt(string $file)
    {
        if (File::decrypt($file, str_replace('.enc', '', $file), $this->encryptionKey)){
            unlink($file);
        }
    }
}

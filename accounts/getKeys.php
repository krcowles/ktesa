<?php
/**
 * This file creates RSA keys and stores them temporarily in the accounts
 * directory. The keys used for the site however will be moved into a private
 * directory of the server, above the project's DOCUMENT_ROOT.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
chdir('../phpseclib1.0.20');
require "Crypt/RSA.php";
$rsa = new Crypt_RSA();
extract($rsa->createKey());

file_put_contents("../data/private.txt", $privatekey);
file_put_contents("../data/public.txt", $publickey);
echo "done";
 
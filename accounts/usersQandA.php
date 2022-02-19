<?php
/**
 * Produce the html for the Security Questions modal showing all the
 * questions and the users current answers. When invoked from the panel
 * menu, the session 'userid' var is available; if not (e.g. change password),
 * the userid must be supplied in the post.
 * PHP Version 7.4
 * 
 * @package Budget
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require_once "../php/global_boot.php";
require "../accounts/security_questions.php";

$userid = isset($_POST['ix']) ?
filter_input(INPUT_POST, 'ix') : $_SESSION['userid'];

chdir('../phpseclib1.0.20');
require "Crypt/RSA.php";
$keyfile = $sitePrivateDir . "/publickey.pem";
$publickey  = file_get_contents($keyfile);
$rsa = new Crypt_RSA();
$rsa->loadKey($publickey);


// retrieve user's data
$user_qandaReq = "SELECT `questions`,`an1`,`an2`,`an3` FROM `USERS` WHERE ".
    "`userid`=?;";
$user_qanda = $pdo->prepare($user_qandaReq);
$user_qanda->execute([$userid]);
$qadata = $user_qanda->fetch(PDO::FETCH_ASSOC);
$userqs = $qadata['questions'];
if (empty($userqs)) { // temporary until all users update security questions
    $uques = [];
    $qa = [];
} else {
    $uques = explode(",", $userqs);
    $a1cipher = hex2bin($qadata['an1']);
    $a2cipher = hex2bin($qadata['an2']);
    $a3cipher = hex2bin($qadata['an3']);
    $qa[0] = $rsa->decrypt($a1cipher);
    $qa[1] = $rsa->decrypt($a2cipher);
    $qa[2] = $rsa->decrypt($a3cipher);
}


// formulate modal body
$body = '';
$ansno = 0;
for ($k=0; $k<10; $k++) {
    $body .= '<span class="ques">' . $questions[$k] . '</span>&nbsp;&nbsp;';
    if (in_array($k, $uques)) {
        $body .= '<input id="q' . $k . '" type="text" name="ans[]" value="' .
            $qa[$ansno++] . '" style="width:220px;" /><br />';
    } else {
        $body .= '<input id="q' . $k . '" type="text" name="ans[]" 
        style="width:220px;" /><br />';
    }
}
echo $body;

<?php
/**
 * This script is required to generate mail via gmail; the host's
 * php mail doesn't seem to work.
 * PHP Version 8.3.9
 *
 * @package Budget
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('America/Denver');

$mail = new PHPMailer();

$mail->isSMTP();
/*
 * Server Configuration
 */
$mail->Host = 'smtp.gmail.com'; // Which SMTP server to use.
$mail->Port = 587; // Which port to use, 587 is the default port for TLS security.
$mail->SMTPSecure = 'tls'; // Which security method to use. TLS is most secure.
$mail->SMTPAuth = true;
$mail->Username = ADMIN;
$mail->Password = GMAIL_ID; // App Specific Password.

// PHPMailer's default connect timeout is 300 seconds. If the host's
// firewall is silently dropping outbound packets (rather than actively
// refusing the connection), that would otherwise hang the AJAX request -
// and the PHP process - for up to 5 minutes before failing. Fail fast
// instead so the failure surfaces quickly and the request doesn't hang.
$mail->Timeout = 15;
$mail->SMTPKeepAlive = false;

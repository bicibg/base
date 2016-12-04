<?php

use Framework\Util\Mailer\PHPMailer;
use Configuration\Config;

if ( ! function_exists('glob_recursive'))
{
    // Does not support flag GLOB_BRACE        
   function glob_recursive($pattern, $flags = 0)
   {
     $files = glob($pattern, $flags);
     foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
     {
     	$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
     }
     return $files;
   }
}

function redirect($url)
{
    header("Location: $url");
}

function send_mail($email,$message,$subject)
{
    $mailConfig = Config::getParam("mail",null);
    if($mailConfig){
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPDebug = $mailConfig["smtpdebug"];
        $mail->SMTPAuth = $mailConfig["smtpauth"];
        $mail->SMTPSecure = $mailConfig["smtpsecure"];
        $mail->Host = $mailConfig["host"];
        $mail->Port = $mailConfig["port"];
        $mail->AddAddress($email);
        $mail->Username = $mailConfig["username"];
        $mail->Password = $mailConfig["password"];
        $mail->SetFrom($mailConfig["frommail"], $mailConfig["fromname"]);
        $mail->AddReplyTo($mailConfig["tomail"], $mailConfig["toname"]);
        $mail->Subject = $subject;
        $mail->MsgHTML($message);
        $mail->Send();
    }else{
        $msg = "<div class='alert alert-error'><button class='close' data-dismiss='alert'>&times;</button>
			    <strong>sorry !</strong> We could not send an activation email at this time</div>";
    }
}



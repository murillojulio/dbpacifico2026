<?php
/**
 * KBlog - KumbiaPHP Blog
 * PHP version 5
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to deivinsontejeda@gmail.com so we can send you a copy immediately.
 *
 * @author Deivinson Tejeda <deivinsontejeda@gmail.com>
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require_once CORE_PATH . 'vendors/phpmailer/src/PHPMailer.php';
require_once CORE_PATH . 'vendors/phpmailer/src/Exception.php';
require_once CORE_PATH . 'vendors/phpmailer/src/SMTP.php';

class CorreoRecoverPassword {

    //Atributos para el envio de correo (acceso privado)
    
    private static $_userName = 'juarmuri@gmail.com'; // mail username
    private static $_password = '82$11$02$b'; // mail password
    private static $_from = 'passrecovery@dbpacifico.creando.net';
    private static $_host = 'smtp.gmail.com';
    private static $_port = 465;
    
    private static $_userName_admin = 'admin@dbpacifico.creando.net'; // mail username    
    private static $_from_admin = 'admin@dbpacifico.creando.net';
    
    /**
     * Genera Claves aleatorias...
     *
     * @param int $length
     */
    public static function generarClave($length)
    {
        $str = 'A7B2C3D4E5F6G7H8B9J&KLMN#PQRSTUVWXYZabcdefghijkbmn$pqrstuvwxyz';
        $cad = '';
        for($i=0;$i<$length;$i++) {
            $cad .= substr($str,rand(0,62),1);
        }
        return $cad;
    }
    /**
     * Envia un correo
     *
     * @param $mail
     * @param $pass
     * @param $body
     */
    public static function send($correo, $person, $body)
    {
        //Cargamos las librería PHPMailer
        //Load::lib('phpmailer');
        //instancia de PHPMailer
        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->SMTPSecure = 'ssl'; // sets the prefix to the servier
        $mail->Host = self::$_host; // sets GMAIL as the SMTP server
        $mail->Port = self::$_port; // set the SMTP port for the GMAIL server
        $mail->Username = self::$_userName;
        $mail->Password = self::$_password;
        $mail->AddReplyTo(self::$_from, 'Administrador del Sistema');
        $mail->From = self::$_from;
        $mail->FromName = 'OBSERVATORIO PACIFICO Y TERRITORIO';
        $mail->Subject = 'Restablecimiento de contraseña';
        $mail->Body = $body;
        $mail->WordWrap = 50; // set word wrap
        $mail->CharSet = 'UTF-8';
        $mail->MsgHTML($body);
        $mail->AddAddress($correo, $person);
        $mail->IsHTML(true); // send as HTML

        //Enviamos el correo
        $exito = $mail->Send();
        $intentos = 1;
        //esto se realizara siempre y cuando la variable $exito contenga como valor false
        while ((!$exito) && $intentos < 1){
            sleep(5);
            $exito = $mail->Send();
            $intentos = $intentos +1;
        }
        return $exito;
    }
    
    
    /**
     * Envia un correo con alerta
     *
     * @param $mail
     * @param $pass
     * @param $body
     */
    public static function send_alert($personas, $asunto, $body)
    {
        /*COMENTADO POR QUE NO ESTA ENVIADO CORREOS
        //Cargamos las librería PHPMailer
        Load::lib('phpmailer');
        //instancia de PHPMailer
        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->SMTPSecure = 'tls'; // sets the prefix to the servier
        $mail->Host = self::$_host; // sets GMAIL as the SMTP server
        $mail->Port = self::$_port; // set the SMTP port for the GMAIL server
        $mail->Username = self::$_userName_admin;
        $mail->Password = self::$_password;
        $mail->AddReplyTo(self::$_from_admin, 'Administrador del Sistema');
        $mail->From = self::$_from_admin;
        $mail->FromName = 'OBSERVATORIO PACIFICO Y TERRITORIO';
        $mail->Subject = $asunto;
        $mail->Body = $body;
        $mail->WordWrap = 50; // set word wrap
        $mail->CharSet = 'UTF-8';
        $mail->MsgHTML($body);        
        
        foreach($personas as $correo => $person) {
            $mail->AddAddress($correo, $person);           
        }
        
        $mail->IsHTML(true); // send as HTML

        //Enviamos el correo
        $exito = $mail->Send();
        $intentos = 1;
        //esto se realizara siempre y cuando la variable $exito contenga como valor false
        while ((!$exito) && $intentos < 1){
            sleep(5);
            $exito = $mail->Send();
            $intentos = $intentos +1;
        }
        return $exito;
         * 
         */
        return TRUE;//esta linea se debe quitar una vez se esten enviando correos
    }

   

}

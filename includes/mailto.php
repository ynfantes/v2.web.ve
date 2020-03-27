<?php
include 'phpmailer/phpmailer.php';
include 'phpmailer/smtp.php';
//include_once 'constants.php';
/**
 * Description of mailto
 *
 * @author emessia
 */
class mailto {
    /**
     * host
     * @var string host
     */
    private $host = SMTP_SERVER;
    
    /**
     * port
     * @var int port
     */
    private $port = PORT;
    
    /**
     * user_email
     * @var string user_mail
     */
    private $user = USER_MAIL;
    
    /**
     * pass_mail
     * @var string pass_mail
     */
    private $pass = PASS_MAIL;
    
    /**
     * Instancia de phpmailer
     * @var phpmailer mail
     */
    private $mail = null;
    
    
    //put your code here
    public function __construct($progamaCorreo=null,$usuario=null,$clave=null) {
        if ($usuario == null) {
            $usuario = $this->user;    
        }
        $pass =  $this->pass;
        $this->mail = new PHPMailer();
        switch($progamaCorreo) {
          case SMTP:
                $this->mail->IsSMTP();
                $this->mail->Host = $this->host;
                $this->mail->Port = $this->port;
                $this->mail->SMTPSecure = "SSL";
                $this->mail->SMTPAuth = true;
                $this->mail->Username = $usuario;
                $this->mail->Password = $clave==NULL? $pass:$clave;
                //die($this->mail->Password);
                $this->mail->From = $usuario;
//                echo "Usuario: ".  $usuario."<br>";
//                echo "Pass: ".$this->pass."<br>";
//                echo "Port: ".$this->port."<br>";
//                echo "Host: ".$this->host."<br>";
                break;
          case sendMail:
              $this->mail->IsSendmail();
              break;
          default:
              $this->mail->IsMail();
        }
        $this->mail->SMTPDebug = false; 
        $this->mail->SMTPKeepAlive = true;
    }
    /**
     * Envía el email
     * @param string $asunto  Asunto del mensaje
     * @param string $mensaje Cuerpo del Mensaje
     * @param string $textoAlternativo Texto alternativo sin formato HTMLK
     * @param string $emailDestinatario   Correo del destinatario
     * @param string $nombreDestinatario  Nombre del Destinatario
     * @param Array $attach  archivos adjuntos
     * @param string $cc  email con copia
     * @param Array $$stringAttachment nombre del archivo adjunto
     * @return string  
     */
    public function enviar_email($asunto,$mensaje, $textoAlternativo, $emailDestinatario, $nombreDestinatario ="",$attach=null,$cc=null,$stringAttachment=null) {
        
        $this->mail->FromName = NOMBRE_APLICACION;
        $this->mail->Subject = $asunto;
        $this->mail->AltBody = $textoAlternativo;
        $this->mail->MsgHTML($mensaje);
        
        if ($attach != null ) {
            foreach ($attach as $value) {
        
                $this->mail->AddAttachment($value);
            }
        }
        if ($stringAttachment!=null) {
            foreach ($stringAttachment as $key => $value) {
                $this->mail->AddStringAttachment($value, $key, 'base64', 'application/pdf');
            }
        }
        if (DEMO == 1) {
            $this->mail->AddAddress('ynfantes@gmail.com','Edgar Messia');
        } else {
            $this->mail->AddAddress($emailDestinatario, $nombreDestinatario);
            if ($cc != null) {
                $this->mail->AddCC($cc);
            }
            $this->mail->AddBCC("ynfantes@gmail.com","Edgar Messia");
        }
        
        $this->mail->IsHTML(true);
        
        if(!$this->mail->Send()) {
          $result = "Error: " .$this->mail->ErrorInfo; //$this->mail->Host."<br>".$this->mail->Port;
        } else {
          $result = "";
        }
        $this->mail->ClearAllRecipients();
        $this->mail->ClearAttachments();
        return $result;
    }
}

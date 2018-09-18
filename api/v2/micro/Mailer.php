<?php
namespace Micro;

class Mailer {
    
    private $_mail;
    private $_config;

    public function __construct($config = array()) {
        $this->_config = $config;
        
        if(strtoupper($this->_config->protocol) != 'RELAY')
        {
            $this->_mail   = new \PHPMailer\PHPMailer\PHPMailer();
            
            switch ($this->_config->protocol) {
                case 'mail':
                    $this->_mail->isMail();
                    break;
                case 'sendmail':
                    $this->_mail->isSendmail();
                    $this->_mail->Host = $this->_config->smtp_host;
                    $this->_mail->Port = $this->_config->smtp_port;
                    break;
                case 'smtp':
                    // $this->_mail->SMTPDebug = TRUE;
                    $this->_mail->isSMTP();
                    $this->_mail->Host      = $this->_config->smtp_host;

                    if ($this->_config->offsetExists('smtp_auth')) {
                        $this->_mail->SMTPAuth = $this->_config->smtp_auth;
                    } else {
                        $this->_mail->SMTPAuth = FALSE;
                    }
                    
                    $this->_mail->Username  = $this->_config->smtp_user;                 
                    $this->_mail->Password  = $this->_config->smtp_pass;                           
                    $this->_mail->SMTPSecure  = $this->_config->smtp_secure;  
                    $this->_mail->Port      = $this->_config->smtp_port;
                    break;
                
                default:
                    $this->_mail->isMail();
                    break;
            }
            
            $this->_mail->isHTML(true);   
            $this->_mail->CharSet = 'UTF-8';
        }
    }

    public function send($params = false){
        if(strtoupper($this->_config->protocol) != 'RELAY')
        {
            if(is_array($params)){
                foreach ($params as $func => $param) {
                    if(method_exists($this, '_'.$func)) $this->{'_'.$func}($param);
                }
            }

            if(!$this->_mail->Send()) {
                return $this->_mail->ErrorInfo;
            }

            // Clear address after send !
            $this->_mail->clearAddresses();
            $this->_mail->clearCCs();
            $this->_mail->clearBCCs();
            $this->_mail->clearAllRecipients();
            $this->_mail->clearAttachments();

            return true;
        }
        else {
            $result = $this->_send_email_curl($params);

            return $result;
        }
    }

    public function from($from = false, $fromName = false){
        $this->_from($from,$fromName);
    }

    public function to($to = false, $toName = false){
        $this->_to($to, $toName);
        // $args = func_get_args();
        // call_user_func_array(array($this,'_to'), $args);
    }

    public function subject($subj= false){
        $this->_subject($subj);
    }

    public function body($body = false){
        $this->_body($body);
    }

    private function _from($from = false, $fromName = false){
        if(is_array($from)){
            foreach($from as $email => $name){
                if(!filter_var($email, FILTER_VALIDATE_EMAIL) && filter_var($name, FILTER_VALIDATE_EMAIL)){
                    $this->_mail->setFrom($name);
                }else{
                    $this->_mail->setFrom($email);
                    $this->_mail->FromName = $name;
                }
            }
        }else{
            $this->_mail->setFrom($from);
        }
        // if($from) 
        // if($fromName) $this->_mail->FromName = $fromName;
    }

    private function _to($to = false, $toName = false){
        if(is_array($to)){
            foreach ($to as $email => $name) {
                if( ! filter_var($email, FILTER_VALIDATE_EMAIL) && filter_var($name, FILTER_VALIDATE_EMAIL)){
                    $this->_mail->addAddress($name);
                } else {
                    $this->_mail->addAddress($email, $name);
                }
            }
        }else{
            if(filter_var($to, FILTER_VALIDATE_EMAIL) && !$toName){
                $this->_mail->addAddress($to);
            }else if(filter_var($to, FILTER_VALIDATE_EMAIL) && $toName){
                $this->_mail->addAddress($to,$toName);
            }
        }
    }

    private function _cc($cc = false){
        if($cc && filter_var($cc, FILTER_VALIDATE_EMAIL)) $this->_mail->addCC($cc);     
    }

    private function _bcc($bcc = false){
        if($bcc && filter_var($bcc, FILTER_VALIDATE_EMAIL)) $this->_mail->addBCC($bcc);     
    }

    private function _subject($subj = false){
        if($subj) $this->_mail->Subject = $subj;
    }

    private function _body($body = false){
        if($body) $this->_mail->Body = $body;
    }

    public function attachment($pathFile=false, $nameFile=false){
        $this->_attachment($pathFile, $nameFile);
    }

    private function _attachment($pathFile=false, $nameFile=false){
        if($pathFile){
            if(is_array($pathFile)){
                foreach ($pathFile as $key => $value) {
                    if(is_array($value)){
                        $this->_mail->addAttachment($value[0], ($value[1] ? $value[1] : $this->get_filename($value[0])));
                    }
                    else
                        $this->_mail->addAttachment($value, $this->get_filename($value));
                }
            }
            else{
                $this->_mail->addAttachment($pathFile, ($nameFile ? $nameFile : $this->get_filename($pathFile)));
            }
        }
    }

    private function get_filename($pathFile){
        $arrFile = explode('/', $pathFile);
        $lentgh  = count($arrFile);
        return $arrFile[($lentgh-1)];
    }


    private function _send_email_curl($params) {
        $params['bcc'] = array('reminder_xlink@kct.co.id');
        
        if (isset($this->_config->protocol))
        {            
            if($params['body']) {
                $params['msg'] = $params['body'];
                unset($params['body']);
            }

            if($params['from']) {
                if(is_array($params['from'])) {
                    $strFrom = '';

                    foreach($params['from'] as $key => $value) {
                        $strFrom = $key;
                    }

                    if($strFrom) {
                        $params['from'] = $strFrom;
                    }
                }
            }

            if($params['to']) {
                if(is_array($params['to'])) {
                    $arrEmailTo = [];

                    foreach ($params['to'] as $email => $name) {
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $arrEmailTo[] = $email;
                        }
                        else if(filter_var($name, FILTER_VALIDATE_EMAIL)) {
                            $arrEmailTo[] = $name;
                        }
                    }

                    $params['to'] = implode(', ', $arrEmailTo);
                }
            }

            if(isset($params['cc'])) {
                if(is_array($params['cc'])) {
                    $arrEmailCc = [];

                    foreach ($params['cc'] as $email => $name) {
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $arrEmailCc[] = $email;
                        }
                        else if(filter_var($name, FILTER_VALIDATE_EMAIL)) {
                            $arrEmailCc[] = $name;
                        }
                    }

                    $params['cc'] = implode(', ', $arrEmailCc);
                }
            }

            if(isset($params['bcc'])) {
                if(is_array($params['bcc'])) {
                    $arrEmailBcc = [];

                    foreach ($params['bcc'] as $email => $name) {
                        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $arrEmailBcc[] = $email;
                        }
                        else if(filter_var($name, FILTER_VALIDATE_EMAIL)) {
                            $arrEmailBcc[] = $name;
                        }
                    }

                    $params['bcc'] = implode(', ', $arrEmailBcc);
                }
            }


            if(isset($params['attachment'])){
                if(is_array($params['attachment'])){

                }else{
                    if (function_exists('finfo_file')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $type = finfo_file($finfo, $params['attachment']);
                        finfo_close($finfo);
                    }else{
                        $type = "";
                    }

                    if (function_exists('curl_file_create')) { // php 5.5+
                      $params['attachment'] = curl_file_create($params['attachment'],$type, basename($params['attachment']));
                    } else { // 
                      $params['attachment'] = '@' . realpath($params['attachment']);
                    }                    
                }
            }

            $ch            = curl_init();
            $options    = array(
                CURLOPT_URL                => $this->_config->relay_url,
                CURLOPT_HEADER            => false,
                CURLOPT_POST            => 1,
                CURLOPT_POSTFIELDS        => $params,
                CURLOPT_RETURNTRANSFER    => TRUE,
                CURLOPT_BINARYTRANSFER    => TRUE
            );
            
            curl_setopt_array($ch, $options);
            
            $output = curl_exec($ch);

            // print_r($params);
            
            curl_close($ch);

            // var_dump($output); exit();
            
            if ($output == '1') return TRUE;
            else return FALSE;
        }
        else return FALSE;
    }

}
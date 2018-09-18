<?php
namespace Micro\Sms;


// require_once(__DIR__.'/../../vendor/php-smpp/php-smpp/smppclient.class.php');
// require_once(__DIR__.'/../../vendor/php-smpp/php-smpp/gsmencoder.class.php');
// require_once(__DIR__.'/../../vendor/php-smpp/php-smpp/sockettransport.class.php');

class SmsProvider {

    protected $_sms;
    protected $_transport;
    protected $_config;


    public function __construct() {
        $app = \Micro\App::getDefault();

        $this->_config = $app->config->smpp;
        if(isset($this->_config)){
            $this->_transport = new \SocketTransport(array($this->_config->host),$this->_config->port);
            $this->_transport->setRecvTimeout(2000);            
            $this->_transport->debug = false;
            
        }

    }

    public function send($tos = null, $msg = null){
        if($tos !== null && $msg !== null){            
            $tos = explode(',', $tos);
            $tos = array_filter($tos, function($val){ return trim($val); });
            foreach ($tos as $to) {
                $this->__send($to, $msg);
            }
        }
        //     try {
        //         $this->_smpp = new \SmppClient($this->_transport);
        //         $this->_smpp->debug = true;

        //         // Open the connection
        //         $this->_transport->open();
        //         $this->_smpp->bindTransmitter($this->_config->user,$this->_config->pass);  
        //         \SmppClient::$sms_null_terminate_octetstrings = false;

        //         // Prepare message
        //         $message = $msg;
        //         $encodedMessage = \GsmEncoder::utf8_to_gsm0338($message);
        //         $from = new \SmppAddress('580',\SMPP::TON_ALPHANUMERIC);
        //         // $to = new SmppAddress('087786772805',SMPP::TON_ALPHANUMERIC);
        //         if(substr($to, 0,2) == '08') $to = substr_replace($to, '628', 0, 2);

        //         $toNum = new \SmppAddress($to,\SMPP::TON_INTERNATIONAL,\SMPP::NPI_E164);

        //         // Send
        //         return $this->_smpp->sendSMS($from,$toNum,$encodedMessage);

        //         // Close connection
        //         $this->_smpp->close();
        //     } catch (Exception $e) {
        //         // var_dump($e);
        //         // return false;
        //     }
        // }
        // return false;
    }

    private function __send($to = null, $msg = null){
        if($to !== null && $msg !== null){            
            $this->_smpp = new \SmppClient($this->_transport);
            $this->_smpp->debug = false;
            $message = $msg;
            $encodedMessage = \GsmEncoder::utf8_to_gsm0338($message);
            $from = new \SmppAddress('580',\SMPP::TON_ALPHANUMERIC);
            // $to = new SmppAddress('087786772805',SMPP::TON_ALPHANUMERIC);
            if(substr($to, 0,2) == '08') $to = substr_replace($to, '628', 0, 2);
            $toNum = new \SmppAddress($to,\SMPP::TON_INTERNATIONAL,\SMPP::NPI_E164);

            try {

                // Open the connection
                $this->_transport->open();
                $this->_smpp->bindTransmitter($this->_config->user,$this->_config->pass);  
                \SmppClient::$sms_null_terminate_octetstrings = false;
                $sending = $this->_smpp->sendSMS($from,$toNum,$encodedMessage);

                // Close connection
                $this->_smpp->close();
                return $sending;
            } catch (\Exception $e) {
                // var_dump($e);
                // return false;
            } catch ( \SmppException $e) {
                
            }
        }
        return false;
    }
}
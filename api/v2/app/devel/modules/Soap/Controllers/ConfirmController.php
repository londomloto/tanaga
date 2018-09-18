<?php
namespace App\Soap\Controllers;

class ConfirmController extends \Micro\Controller {

    public function serviceAction() {

        $namespace = $this->url->getSiteUrl('soap/confirm/service');

        $server = new \nusoap_server();
        
        $server->configureWSDL(
            'SI_CONFIRM_SOService',
            $namespace,
            $namespace,
            'document'
        );

        $server->wsdl->addComplexType(
            'DATA_TYPE',
            'complexType',
            'struct',
            'sequence',
            '',
            array(
                'ORDER_NUMBER' => array('name' => 'ORDER_NUMBER', 'type' => 'xsd:string')
            )
        );

        $server->wsdl->addComplexType(
            'RESULT_TYPE',
            'complexType',
            'struct',
            'sequence',
            '',
            array(
                'RESULT' => array('name' => 'RESULT', 'type' => 'xsd:string'),
                'MESSAGE' => array('name' => 'MESSAGE', 'type' => 'xsd:string')
            )
        );

        $server->wsdl->addComplexType(
            'DT_CONFIRM',
            'complexType',
            'struct',
            'sequence',
            '',
            array(
                'INTERFACE_ID' => array('name' => 'INTERFACE_ID', 'type' => 'xsd:string'),
                'CLIENT_ID' => array('name' => 'CLIENT_ID', 'type' => 'xsd:string'),
                'DATA' => array('name' => 'DATA', 'type' => 'tns:DATA_TYPE')
            )
        );

        $server->wsdl->addComplexType(
            'DT_MOS_RESULT',
            'complexType',
            'struct',
            'sequence',
            '',
            array(
                'INTERFACE_ID' => array('name' => 'INTERFACE_ID', 'type' => 'xsd:string'),
                'SAP_ID' => array('name' => 'SAP_ID', 'type' => 'xsd:string'),
                'RESULT' => array('name' => 'RESULT', 'type' => 'tns:RESULT_TYPE')
            )
        );

        $server->register(
            'App.Soap.Controllers.ConfirmController.Service',
            array(
                'MT_CONFIRM' => 'tns:DT_CONFIRM'
            ),
            array(
                'MT_MOS_RESULT' => 'tns:DT_MOS_RESULT'
            ),
            FALSE,
            $namespace,
            'document',
            'literal'
        );
        
        $server->service($this->request->getRawBody());
        exit();
    }

    public static function Service($input) {
        return array(
            'MT_MOS_RESULT' => array(
                'INTERFACE_ID' => 'Your INTERFACE_ID',
                'SAP_ID' => 'Your SAP_ID',
                'RESULT' => array(
                    'RESULT' => 'Success',
                    'MESSAGE' => 'Everything is OK'
                )
            )
        );
    }
}
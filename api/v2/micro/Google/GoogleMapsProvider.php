<?php
namespace Micro\Google;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\RequestException;

class GoogleMapsProvider extends \Micro\Component {
    
    public function __construct() {
        $this->__config = $this->getApp()->config->google->maps;
    }
    
    public function find($input, $types = 'address') {

        $client = new Client();
        $result = array();

        try {
            $response = $client->get(
                'https://maps.googleapis.com/maps/api/place/autocomplete/json',
                array(
                    'query' => array(
                        'input' => $input,
                        'types' => $types,
                        'key' => $this->__config->key
                    )
                )
            );

            $json = json_decode($response->getBody(), TRUE);

            if ($json['status'] == 'OK') {
                foreach($json['predictions'] as $item) {
                    $result[] = array(
                        'place_id' => $item['place_id'],
                        'description' => $item['description']
                    );
                }
            }
            
        } catch(RequestException $ex) {}

        return $result;
    }

}
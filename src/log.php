<?php

namespace loggly;

use \Requests;

class log{

    private $endpoint;


    public function __construct( $endpoint ){
        $this->endpoint = $endpoint;
    }


    public function parseMessage( $message ){
        if( is_array( $message ) ){

            // add timestamp
            if( !isset( $message[ 'timestamp' ] ) ) {
                $message['timestamp'] = date(sprintf('Y-m-d\TH:i:s%s\Z', substr(microtime(), 1, 7)));
            }

            return json_encode( $message );
        }

        return $message;
    }


    public function parseMessageMultiple( $message ){
        if( is_array( $message ) ) {
            return array_map( array( $this, 'parseMessage' ), $message );
        }else {
            return $this->parseMessage( $message );
        }
    }


    public function send( $message ){

        $headers = array(
            'Content-Type' => 'text/plain'
        );

        $message = $this->parseMessageMultiple( $message );

        // check if multiple messages
        if( is_array( $message ) ){

            $message = implode( "\n", $message );

            $req = Requests::post( 'http://logs-01.loggly.com/bulk/' . $this->endpoint . '/tag/bulk/', $headers, $message );
        }else{
            $req = Requests::post( 'http://logs-01.loggly.com/inputs/'. $this->endpoint . '/tag/http/', $headers, $message );
        }

        $result = json_decode( $req->body, true );

        return $req->status_code == 200 && isset( $result['response'] ) && $result['response'] == 'ok';
    }

}

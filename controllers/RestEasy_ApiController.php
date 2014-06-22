<?php
namespace Craft;

class RestEasy_ApiController extends BaseController
{

    protected $allowAnonymous = true;

    private $response;

    public function __construct()
    {
        // Disable debugging
        craft()->config->set('devMode', false, ConfigFile::General);

        // Start building the response
        $this->response['time'] = microtime();
        $this->response['status'] = 200;
        $this->response['id'] = md5( craft()->request->getPath() . '?' . craft()->request->getQueryStringWithoutPath() );
        $this->response['criteria'] = '';
    }

    public function actionRequest()
    {
        // Get the current request method
        $method = strtolower( $_SERVER['REQUEST_METHOD'] );

        // Call the request method's service
        $response = craft()->{ 'restEasy_' . $method }->run( $this->getActionParams() );

        //
        $this->response = array_merge( $this->response, $response );

        // Send the response
        $this->_respond();

    }

    private function _respond()
    {
        // Add errors to the response if there are any
        if( ! empty( $this->errors ) ) {
            $this->response['status'] = array_keys( $this->errors )[0];
            $this->response['errors'] = $this->errors;
        }

        // Calculate the total response time
        $this->response['time'] = microtime() - $this->response['time'];

        // Return json
        $this->returnJson( $this->response );
    }

    private function _dump($data)
    {
        echo '<pre>';
        var_dump( $data );
        echo '</pre>';
        die();
    }

}

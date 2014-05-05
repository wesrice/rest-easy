<?php
namespace Craft;

require __DIR__ . '/../vendor/autoload.php';

use League\Fractal;

class RestEasy_ApiController extends BaseController
{

    protected $allowAnonymous = true;
    // protected $allowAnonymous = array('actionList');

    private $fieldTypes;
    private $routeParams;
    private $elementType;
    private $params;
    private $response;
    private $errors = array();

    public function __construct()
    {
        // Disable debugging
        craft()->config->set('devMode', false, ConfigFile::General);

        // Get the route params
        $this->routeParams = $this->getActionParams();

        // Get the element type
        $this->elementType = ucwords( $this->routeParams['variables']['elementType'] );

        // Merge the route params with the $_GET params
        $this->params = $this->_get_params();

        // Start building the response
        $this->response['time'] = microtime();
        $this->response['status'] = 200;
        $this->response['id'] = md5( craft()->request->getPath() . '?' . craft()->request->getQueryStringWithoutPath() );
        $this->response['elementType'] = $this->elementType;
        $this->response['criteria'] = $this->params;

    }

    public function actionRequest()
    {
        // Get the current request method
        $method = strtolower( $_SERVER['REQUEST_METHOD'] );

        // Call the appropriate class method based on the request method
        if( method_exists( get_class(), '_' . $method ) ) {
            $this->{ '_' . $method }();
        } else {
            $this->errors[405] = get_class() . '->_' . $method . ' does not exist.';
        }

        // Send the response
        $this->_respond();

    }

    private function _get()
    {

        $criteria = craft()->elements->getCriteria( ElementType::$this->elementType );

        foreach( $this->params as $key => $value )
        {
            $criteria->{ $key } = $value;
        }

        // $results = $criteria->find();
        $results = TemplateHelper::paginateCriteria( $criteria );

        // $this->response['criteria'] = $criteria;

        // Add the total results to the page
        $this->response['pagination'] = $results[0];

        // Remove the pagination
        unset( $results[0] );

        // Make $results the returned elements
        $results = $results[1];

        // Loop through each result
        foreach( $results as $key => $result ) {

            // Get the fields for this element
            $fields = \Craft\RestEasy_TransformerHelper::_getFields( $result );

            // Convert the object to an array - hacky as fuck
            $result = \CJSON::decode( \CJSON::encode( $result ) );

            // Add the fields to the element
            if( ! empty( $fields ) )
                $result['fields'] = $fields;

            // Replace the object in the results array with an array
            $results[$key] = $result;

        }

        // If there are results, add them to the data property
        if( $results ) $this->response['data'] = $results;
        if( count( $this->response['data'] ) == 1 ) $this->response['data']  = $this->response['data'][0];

    }

    private function _get_params()
    {
        $routeParams = $this->routeParams['variables'];
        unset( $routeParams['matches'] );
        unset( $routeParams['elementType'] );

        $get = $_GET;
        unset( $get['p'] );

        return array_merge( $routeParams, $get );
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

}

<?php
namespace Craft;

class RestEasy_GetService extends BaseApplicationComponent
{
    public $response = array( 'pagination' => '', 'data' => '' );
    private $resteasy_params = array( 'fields', 'embed' );
    private $params;
    private $errors = array();

    public function run( $actionParams )
    {
        // Get the params
        $this->_get_params( $actionParams );

        // Get the elements
        $elements = $this->_getElements();

        // Get the fields for each elements
        $elements = $this->_getFields( $elements, $this->params );

        // Filter the nodes
        $this->response['data'] = $this->_filterNodes( $elements );

        // Return the response
        return ( empty( $this->errors ) ) ? $this->response : array( 'errors' => $this->errors );
    }

    private function _getElements()
    {

        // Get the the element type
        $criteria = craft()->elements->getCriteria( $this->params['elementType'] );

        // Set the rest of the criteria
        foreach( $this->params['craft'] as $key => $value )
        {
            $criteria->{ $key } = $value;
        }

        // Get elements that match the criteria, paginated
        $results = TemplateHelper::paginateCriteria( $criteria );

        // Add the total results to the page
        $this->response['pagination'] = $results[0];

        // Remove the pagination node
        unset( $results[0] );

        // Return the elements
        return $results[1];

    }

    private function _getEmbedHandles( $embeds )
    {
        $handles = array();

        foreach( $embeds as $key => $embed ) {

            $fields = array();
            $handle_parts = explode( '.', $embed );
            for( $i = 0; $i < count( $handle_parts ); $i++ ) {
                $string = $handle_parts[$i];

                if( isset( $fields[$i - 1] ) ) {
                    $fields[] = $fields[$i - 1] . '.' . $string;
                } else {
                    $fields[] = $string;
                }

            }

            $handles = array_merge( $handles, $fields );

        }

        $handles = array_unique( $handles );

        return $handles;

    }

    private function _getFields( $elements, $params )
    {
        // Set the array of handles for the fields that are needed
        $handles = isset( $params['resteasy']['embed'] ) ? $this->_getEmbedHandles( $params['resteasy']['embed'] ) : array();

        // Loop through each element
        foreach( $elements as $key => $element ) {

            // Get the fields for this element
            $fields = $this->_getElementFields( $element, $handles );

            // Convert the object to an array - hacky as fuck
            $element = \CJSON::decode( \CJSON::encode( $element ) );

            // Add the fields to the element
            if( ! empty( $fields ) )
                $element = array_merge( $element, $fields );

            // Replace the object in the elements array with an array
            $elements[$key] = $element;

        }

        return $elements;

    }

    public function _getElementFields( $element, $handles, $parent_handle = '' )
    {
        $data = array();

        foreach( $handles as $handle ) {

            $index = explode('.', $handle);

            $result = $this->_getElementFieldValue($index, $element);

            $result = \CJSON::decode( \CJSON::encode( $result ) );

            $data = \CMap::mergeArray( $data, $result );

        }

        return $data;

    }

    private function _getElementFieldValue( $index, $value, $depth = 0 )
    {

        $data = array();

        if( is_array( $index ) && count( $index ) ) {
            $current_index = array_shift( $index );
        } elseif( is_string( $index ) && ! empty( $index ) ) {
            $current_index = $index;
        } else {
            return $value;
        }

        if( is_array( $index ) &&
            count( $index ) &&
            is_array( $value[$current_index] ) &&
            count( $value[$current_index] ) ) {

                $data[$current_index] = $this->_getElementFieldValue($index, $value[$current_index], $depth + 1);

        } else {

            if( isset( $value[$current_index] ) && $value[$current_index] instanceof \Craft\ElementCriteriaModel ) {

                $nodes = $value[$current_index]->find();

                foreach( $nodes as $node ) {

                    $data[$current_index]['eid-' . $node->id] = $this->_getElementFieldValue( $index, $node, $depth + 1);
                }

            } elseif( isset( $value[$current_index] ) && $value[$current_index] instanceof \Craft\RichTextData ) {

                $data[$current_index] = $value[$current_index]->getRawContent();

            } elseif( isset( $value[$current_index] ) ) {

                $data[$current_index] = $value[$current_index];

            } else {

                $data[$current_index] = null;

            }

        }

        return $data;

    }

    private function _get_params( $actionParams )
    {

        // Get the element type
        $elementType = ucwords( $actionParams['variables']['matches']['elementType'] );

        $resteasy_params = array();

        unset( $actionParams['variables']['matches'] );
        unset( $actionParams['variables']['elementType'] );

        $get = $_GET;
        unset( $get['p'] );

        $get = array_merge( $actionParams['variables'], $get );

        foreach( $this->resteasy_params as $key => $param ) {
            if( isset( $get[$param] ) ) {
                $resteasy_params[$param] = explode( ',', $get[$param] );
                unset( $get[$param] );
            }
        }

        $this->params = array(
            'elementType' => $elementType,
            'resteasy' => $resteasy_params,
            'craft'    => $get
        );

        $this->response['criteria'] = $this->params;

    }

    private function _filterNodes( $node, $depth = 0 )
    {

        if( ! is_array( $node ) ) {

            if( is_numeric( $node ) )
                $node = (float) $node;

            return $node;
        }

        $keys = array_keys( $node );
        $matches = preg_grep( '/eid-/', $keys );

        if( ! empty( $matches ) ) {
            $node = array_values( $node );
        }

        foreach($node as $key => $value) {

            $node[$key] = $this->_filterNodes($value, $depth + 1);

        }

        return $node;

    }

    public static function dump($data)
    {
        echo '<pre>';
        var_dump( $data );
        echo '</pre>';
        die();
    }

}

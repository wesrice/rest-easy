<?php
namespace Craft;

ini_set('memory_limit', '1024M');

class RestEasy_TransformerHelper
{
    public static $transformers = array(
        'Assets'       => '\Craft\RestEasy_TransformerHelper::returnElements',
        'Categories'   => '\Craft\RestEasy_TransformerHelper::returnElements',
        'Checkboxes'   => '\Craft\RestEasy_TransformerHelper::returnArray',
        'Entries'      => '\Craft\RestEasy_TransformerHelper::returnElements',
        'Matrix'       => '\Craft\RestEasy_TransformerHelper::returnElements',
        'MultiSelect'  => '\Craft\RestEasy_TransformerHelper::returnArray',
        'RichText'     => '\Craft\RestEasy_TransformerHelper::fieldRichText',
        'Tags'         => '\Craft\RestEasy_TransformerHelper::returnArray',
        'Users'        => '\Craft\RestEasy_TransformerHelper::returnElements'
    );

    public static function returnArray($array)
    {
        $data = array();
        foreach($array as $item) {
            $data[] = $item;
        }
        return $data;
    }

    public static function returnElements($elements)
    {

        $elementsData = array();

        foreach( $elements as $key => $element ){

            // Get the fields for this element
            $fields = self::_getFields( $element );

            // Convert the object to an array - hacky as fuck
            $element = \CJSON::decode( \CJSON::encode( $element ) );

            // Add the fields to the element
            if( ! empty( $fields ) )
                $element['fields'] = $fields;

            // Replace the object in the results array with an array
            $elementsData[$key] = $element;

        }

        return $elementsData;

    }

    public static function fieldRichText($richText)
    {
        return $richText->getRawContent();
    }

    public function _getFields( $element )
    {

        // Get the fields of the element
        $fields = $element->getFieldLayout()->getFields();

        // Create an array that data will be added to
        $elementData = array();

        foreach( $fields as $field )
        {

            // Get the field object
            $field = $field->getField();

            // Get the field handle
            $handle = $field->handle;

            // Get the field value
            $value = $element->$handle;

            // If this field has a transformer, run it
            if( array_key_exists( $field->type, \Craft\RestEasy_TransformerHelper::$transformers ) && $value !== null ) {

                // Get the transformer class and method location
                $class_method = explode( '::', \Craft\RestEasy_TransformerHelper::$transformers[ $field->type ] );

                // Get the class
                $class = $class_method[0];

                // Get the method
                $method = $class_method[1];

                // Transform the data based on what type of field it is
                $value = $class::$method( $value );

            }

            if( ! empty( $value ) )
                $elementData[$handle] = $value;

        }

        return $elementData;

    }

    public static function dump($data)
    {
        echo '<pre>';
        var_dump( $data );
        echo '</pre>';
        die();
    }

}

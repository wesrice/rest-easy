<?php
namespace Craft;

class RestEasyPlugin extends BasePlugin
{

    function getName()
    {
        return Craft::t( 'REST Easy' );
    }

    function getVersion()
    {
        return '0.0';
    }

    function getDeveloper()
    {
        return 'Wes Rice';
    }

    function getDeveloperUrl()
    {
        return 'https://twitter.com/wesrice';
    }

    function hasCpSection(){
        return false;
    }

}

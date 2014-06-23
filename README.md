REST Easy
=========

REST Easy is a plugin for Craft (http://buildwithcraft.com) that allows for enteration with Craft via a simple API.

## Setup
1. Install and enable the plugin
2. Add the following routes to `craft/config/routes.php`.
```php
return array(
    'api/(?P<elementType>.*)/(?P<id>\d)'      => array('action' => 'restEasy/api/request'),
    'api/(?P<elementType>.*)'                 => array('action' => 'restEasy/api/request'),
);
```
**Optional** - Install the JSON Formatter extension for Google Chrome. This nifty extension formats json output in a collapsable tree. https://chrome.google.com/webstore/detail/json-formatter/bcjindcccaagfpapjjmafapmmgkkhgoa?hl=en

## Usage
The general structure of the API endpoint is `http://{name-of-your-site}.com/api/{elementType}/{id}`. The `elementType` is required and the `id` is optional. If only the `elementType` is supplied, then you get a listing of that element type, with a default limit of 100 elements (the Craft default). Native element types include `Entry`, `User`, `Asset`, and `Category` among others. You can also use the lowercase versions of these element types in the uri.

The data that is returned in the response is the model of the element type.

### Criteria

If you want to filter the results by specific criteria, append the criteria parameters that you want to filter by in the querystring. For example, if you have a section on the site called `news`, you can get only the news entries by targeting `http://{name-of-your-site}.com/api/entry?section=news`. Currently, all string based parameters that can be passed to an `ElementCriteriaModel` are supported.

### Embeds
Embeds are a way to retrieve additional data that describes an element. For example, to retrieve a custom field of `summary` of a news entries, target `http://{name-of-your-site}.com/api/entry?section=news&embed=summary`.


## To-Do
* Add config file for setting authentication keys and default embeds
* Post
* Patch
* Put
* Delete

REST Easy
=========

REST Easy is a plugin for Craft (http://buildwithcraft.com) that allows for enteration with Craft via a simple API.

### Setup
1. Install and enable the plugin
2. Add the following routes to `craft/config/routes.php`.
```php
return array(
    'api/(?P<elementType>.*)/(?P<id>\d)'      => array('action' => 'restEasy/api/request'),
    'api/(?P<elementType>.*)'                 => array('action' => 'restEasy/api/request'),
);
```
3. **Optional** Install the JSON Formatter extension for Google Chrome. This nifty extension formats json output in a collapsable tree. https://chrome.google.com/webstore/detail/json-formatter/bcjindcccaagfpapjjmafapmmgkkhgoa?hl=en

### Usage
The general structure of the API endpoint `http://{name-of-your-site}.com/api/{elementType}/{id}`. The `elementType` is required and the `id` is optional. If only the `elementType` is supplied, then you get a listing (with a default limit of 100) of that element type. Native element types include `Entry`, `User`, `Asset`, and `Category` among others. You can also use the lowercase versions of these element types in the uri. 

If you want to filter the results, append the parameters that you want to filter by in the querystring. For example, if you have a section on the site called `news`, you can get only the news entries by targeting `http://{name-of-your-site}.com/api/entry?section=news`.

The data that is returned in the response is the basic model of the element type. In addition, all of the custom fields are retrieved automatically and in a nested fashion. This means that if you have an entry with 5 custom fields that is related to an entry with 9 custom fields, the parent entry will return with its 5 custom fields tied to the entry and the related entry will also have its 9 custom fields.


### To-Do
* Move business logic into a service
* _post()
* _delete()
* Hooks to extend transforms for custom field types
* oAuth 2.0 integration

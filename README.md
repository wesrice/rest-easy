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


### To-Do
* Move business logic into a service
* _post()
* _delete()
* Hooks to extend transforms for custom field types

Yii2 rest authorized
====================
This extension increase security betwean requests to REST contorllers.

How does it work: there is a short(token) and long(series) keys. Short key changes every time, the long key remains the same for entire period of authorization.

Then they are merged into a string and attached to the Authorization header. These values are separated by ";"

To confirm next request, when "client" send new request, it  attaches the same Authoriztion header with the data it recieved.
And this continues until the user is logged out or the keys are stolen.

When the keys are stolen and the thief use the user's data - the short key (token)  changes every request. When the real user makes a request - the system will notice that long key (series) is the same, but short key doesn't match. In this case system delete  Authorization, the thief and the real user will be logged out

For data storage it uses ActiveRecord table. In this table keeps all authorization data, the end date of the session. Sessions are stored in Redis.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist gud3/yii2-rest-auth "*"
```

or add

```
"gud3/yii2-rest-auth": "*"
```

to the require section of your `composer.json` file.


Need
----

You need to override the static function in the 'Users' table:
```php
public static function findIdentityByAccessToken($id, $type = null)
{
    return static::find()->where(['id' => $id])->one() || false;
}
```


Usage
-----

To use this extension, simply add the following code in your controller behaviors:

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    
    $auth = ['index'];
    //$auth = ['index', 'update', 'create', 'etc..'];
    $behaviors['authenticator']['class'] = \gud3\restAuth\CheckToken::className();
    $behaviors['authenticator']['only'] = $auth;

    return $behaviors;
}
```

For check exist Authorized data in headers:

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    
    $auth = [];
    
    if (\gud3\restAuth\CheckToken::isAuth()) {
    array_push($auth, 'index', 'create');
    }
        
    $behaviors['authenticator']['class'] = \gud3\restAuth\CheckToken::className();
    $behaviors['authenticator']['only'] = $auth;
    
    return $behaviors;
}
```
This is necessary to check if there are authorization data, then check them, and if it is successful, authorize or go through the system without authorization, then Yii::$app->user->isGuest = true


Change storage
--------------

To store the session in the radish, you need to  :

```php
'components' => [
    'cache' => [
        'class' => 'yii\redis\Cache',
    ],
]
```
Yii2 rest authorized
====================
Authorization for rest, which is made for increased security.

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
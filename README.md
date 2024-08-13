weebz/yii2-basics
======================
Basics Features for yii2

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require weebz/yii2-basics
```

or add

```
"weebz/yii2-basics": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, you can config the path mappings of the view component:

```php
    'modules' => [
        //'common' => [ 'class' => 'app\modules\common\Module', ],
        'common' => [ 'class' => '\weebz\yii2basics\Module', ]
    ],
```

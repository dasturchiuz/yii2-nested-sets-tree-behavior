NestedSetsTreeBehavior
======================
NestedSetsTreeBehavior this is the connector between
https://github.com/creocoder/yii2-nested-sets
and
https://github.com/wbraganca/yii2-fancytree-widget


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require dasturchiuz/yii2-nested-sets-tree-behavior
```

or add

```
"dasturchiuz/yii2-nested-sets-tree-behavior": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your model  :

```php
'htmlTree'=>[
    'class' => \dasturchiuz\treebehavior\NestedSetsTreeBehavior::className()
]
```
use
```php
SomeModel::findOne($id)->tree()
```
to get array for fancytree-widget



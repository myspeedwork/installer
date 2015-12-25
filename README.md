speedwork-installer
===================================

A composer plugin, to install differenty types of composer packages in custom directories outside the default composer default installation path which is in the `vendor` folder.

Installation
------------

- Include the composer plugin into your `composer.json` `require` section::

```
  "require":{
    "php": ">=5.3",
    "speedwork/installer": "dev-master"
  }
```

Manage assests in components, modules and widgets

```
  "extra": {
      "assets-dir" : "public/assets",
      "assets" : [
        {
          "type" : "directory",
          "name" : "assets",
          "target" : "assets"
        }
      ]
  }
```
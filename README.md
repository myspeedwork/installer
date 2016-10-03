speedwork-installer
===================================
[![codecov](https://codecov.io/gh/speedwork/installer/branch/master/graph/badge.svg)](https://codecov.io/gh/speedwork/installer)
[![StyleCI](https://styleci.io/repos/15472515/shield)](https://styleci.io/repos/15472515)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/771d6248-d38a-43bd-8a0a-56d8b40d2e17/mini.png)](https://insight.sensiolabs.com/projects/771d6248-d38a-43bd-8a0a-56d8b40d2e17)
[![Latest Stable Version](https://poser.pugx.org/speedwork/installer/v/stable)](https://packagist.org/packages/speedwork/installer)
[![Latest Unstable Version](https://poser.pugx.org/speedwork/installer/v/unstable)](https://packagist.org/packages/speedwork/installer)
[![License](https://poser.pugx.org/speedwork/installer/license)](https://packagist.org/packages/speedwork/installer)
[![Total Downloads](https://poser.pugx.org/speedwork/installer/downloads)](https://packagist.org/packages/speedwork/installer)
[![Build status](https://ci.appveyor.com/api/projects/status/10aw52t4ga4kek27?svg=true)](https://ci.appveyor.com/project/2stech/installer)
[![Build Status](https://travis-ci.org/speedwork/installer.svg?branch=master)](https://travis-ci.org/speedwork/installer)

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
      "assets-dir" : "mypackage",
      "assets" : {
        "src/mypackage/assets/*.js" : "js/",
        "src/mypackage/assets/*.css" : "css/",
        "src/mypackage/assets/fonts/*" : "fonts/"
     }
  }
```

Contributing
------------

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Make your changes
4. Run the tests, adding new ones for your own code if necessary (`phpunit`)
5. Commit your changes (`git commit -am 'Added some feature'`)
6. Push to the branch (`git push origin my-new-feature`)
7. Create new Pull Request

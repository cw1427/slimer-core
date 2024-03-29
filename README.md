# Slimer-core : Slimer framework basic core library

## Release history
- v1.2.6 Set Slim app instance handler into slimer router.
- v1.2.5 Add Auth middleware request quary parameter to login redirect route.
- v1.2.4 fix bug for httpclient initial parameter overwrite bug.
- v1.2.3 add isNeedHint twig template function.
- v1.2.1 Bug fix for NavBarExtension AvatarFunction
- v1.2.0 Add php5 fatal error exception handler.
- v1.1.6 Add route middleware feature in route config.
- v1.1.5 Add RbacPermissionNotFoundException catch in SideBarExtension::isMenuVisible function.
- v1.1.4 Add jump to with parameter case support.
- v1.1.3 Bug fix for intro feature would miss render when first login.
- v1.1.2 add auth middlewoare jump to next route name to allow jump to feature.

It works with Slimer framework. Please refer skeleton framework: https://github.com/cw1427/Slimer

## Installation

Install the latest version with

```bash
$ composer require slimer/slimer-core
```

## Basic Usage

--Todo

- Router

- Provider

- Controller

- Config

- Auth

- Orm

- Html



## Documentation

- [Feature introduction](doc/01-usage.md)

## Third Party Packages

slimer-core depends many third party packages. For example: slim3, medoo, php-rbac and so on.


## About

Slimer-core is the fundamental core library for the skeleton framework:https://github.com/cw1427/Slimer

Please use cw/1427/Slimer to start your trial.

### Requirements

- slimer-core 1.0 works on PHP5.6

### Submitting bugs and feature requests

Bugs and feature request are tracked on [GitHub](https://github.com/Seldaek/monolog/issues)


### Author

Shawn Chen(cwvinus@163.com)

### License

Monolog is licensed under the MIT License - see the `LICENSE` file for details

### Acknowledgements
<!--stackedit_data:
eyJoaXN0b3J5IjpbLTEyOTYwMjE1MzRdfQ==
-->
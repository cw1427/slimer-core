### 1.2.5 (2022-03-23)
  -- New feature. To add Auth middleware to pass req quary parameter to login to route.
### 1.2.4 (2021-07-12)
  -- Bug fix for httpclient initializtion parameter overwrite bug.
### 1.2.1 (2020-04-22)
  -- Bug fix for NavBarExtension AvatarFunction.
### 1.2.0 (2020-04-17)
  -- Add fatal error handler for php5 to handle all of the php fatal exception that can't be catched.
### 1.1.6 (2020-03-19)
  -- Add route middleware feature.
### 1.1.5 (2020-02-20)
  -- Add Add RbacPermissionNotFoundException catch in SideBarExtension::isMenuVisible function.
### 1.1.4 (2019-12-16)
  -- Add jump to with parameter case support.
### 1.1.3 (2019-11-22)
  -- Bug fix for intro feature when first login not showing.
### 1.1.1 (2019-7-30)
  -- Adjust rbacmanager command compatability for sqlite db.
### 1.1.0 (2019-7-12)
  -- Adjust some model level compatibilty for sqlite and adjust the menu visubile support for default Index controller.
### 1.0.8 (2019-5-24)
  -- Bug fix for the recrision meuc isSubMenuActive checking logic wrong.
### 1.0.7 (2019-5-20)
  -- Add menu isActive fit for recusion case more than 2 level menu.
### 1.0.6 (2018-12-30)
  -- Add isMenuVisible feature to hide menu by permission
### 1.0.5 (2019-02-15)
  -- Fix some bug and add isMenuVisible twig extension function to bring menu hidden by permission.
### 1.0.4 (2018-12-30)
  -- Add tasks,notice,actions config support.
### 1.0.3 (2018-12-26)
  -- Test for deploy into local composer registry.
### 1.0.2 (2018-12-26)
  -- Change the httpbasic authen provider httpauth_middleware default config setup.
### 1.0.0 (2018-12-10)
  -- Split slimer-core out from Slimer framework
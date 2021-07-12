<?php
/**
 * Author: Shawn Chen
 * Desc: The Slimer basic provider to extend the Slimer container by adding the config, and app_router feature
 */

namespace Slimer;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slimer\Router;
use Slimer\Config;
use Exception;

/**
 * Slimer Service Provider.
 */
class Provider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['suit_config'] = function ($c) {
            return new Config($c);
        };
        $container['config'] = $container->protect(function ($string, $default = null) use ($container) {
            return $container['suit_config']->__invoke($string, $default);
        });
        $container['app_router'] = function ($c) {
            return new Router($c);
        };
        $container['globalrequest_middleware'] = $container->protect(function ($request, $response, $next) use ($container) {
            if ($container->has('request')) {
                unset($container['request']);
                $container['request'] = $request;
            }

            return $next($request, $response);
        });

        $container['appErrorHandler'] = function ($c) {
            return new \Slimer\ErrorHandler($c);
        };
        $container['notFoundHandler'] = function ($c) {
            return function (ServerRequestInterface $request, ResponseInterface $response) use ($c) {
                return $c['appErrorHandler']->error404($request, $response);
            };
        };

        $container['controller'] = $this->setControllerLoader($container);
        $container['routeMiddleware'] = $this->setRouteMiddlewareLoader($container);
        $container['errorHandler'] = $this->setErrorHandler($container);
        $container['phpErrorHandler'] = $this->setErrorHandler($container);
        $container['commandRunner'] = function() use ($container) {
            if (PHP_SAPI == 'cli') {
                global $argv;
                $argv[1] = strtolower($argv[1]);
            }
            return new \adrianfalleiro\SlimCLIRunner($container);
        };

        $container['smtpMailer'] = function () use ($container) {
            return new \Nette\Mail\SmtpMailer($container['config']('mail'));
        };
        $container['smtpMessage'] = $container->factory(function () use ($container) {
            return new \Nette\Mail\Message();
        });
        $container['shellCommand'] = $container->protect(function ($command) use ($container) {
            return new \mikehaertl\shellcommand\Command($command);
        });
        $container['httpClient'] = $container->protect(function ($configArray=[]) use ($container) {
            $ca = \array_merge(['timeout'=>60,'verify'=>false], $configArray);
            return new \GuzzleHttp\Client($ca);
        });
        if ('prod' === \getenv('APP_ENV')){
            \ini_set('display_errors', 0);
            \set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($container) {

                if (!(error_reporting() & $errno)) {
                    // This error code is not included in error_reporting, so let it fall
                    // through to the standard PHP error handler
                    return false;
                }
                //----php5 convert those unthrowable error as the ErrorException.
                // It is too aggressive change to directly throw exception especially for some forgivable error
                // instead change to records the error in log
                $container['logger']->error("{$errstr} on {$errfile} at {$errline}");
                //throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);

            });
            //----regist php5 fatel error handler  if for php7 all of the errors are implement with Throwable can be catch by Slim as default
            \register_shutdown_function(function () use ($container) {
                $error = error_get_last();
                if (empty($error)) {
                    return;
                }else{
                    if ($error['type']==1){
                        //----fatal error
                        if ($container->has('appErrorHandler')) {
                            die( $container['appErrorHandler']->fatal500(new Exception("{$error['message']} - {$error['file']} - {$error['line']}")));
                        }
                    }
                    return;
                }

            });

        }
    }

    /**
     * Set controller() function into container.
     *
     * @param Container $container
     *
     * @return callable
     */
    protected function setControllerLoader(Container $container)
    {
        return $container->protect(function ($name,$clz=null) use ($container) {
            $parts = \explode('_', $name);
            $class = (isset($clz) && $clz !=null) ? $clz : $container['config']('suit.namespaces.controller', '\\App\\Controller\\');
            foreach ($parts as $part) {
                $class .= \ucfirst($part);
            }
            if (!$container->has('controller_'.$class)) {
                $container['controller_'.$class] = function ($container) use ($class) {
                    return new $class($container);
                };
            }

            return $container['controller_'.$class];
        });
    }

    /**
     * Set route middleware() function into container.
     *
     * @param Container $container
     *
     * @return callable
     */
    protected function setRouteMiddlewareLoader(Container $container)
    {
        return $container->protect(function ($name,$clz=null) use ($container) {
            $parts = \explode('_', $name);
            $class = (isset($clz) && $clz !=null) ? $clz : $container['config']('suit.namespaces.route_middleware', '\\App\\Middleware\\');
            foreach ($parts as $part) {
                $class .= \ucfirst($part);
            }
            if (!$container->has('middleware_'.$class)) {
                $container['middleware_'.$class] = function ($container) use ($class) {
                    return new $class($container);
                };
            }

            return $container['middleware_'.$class];
        });
    }

    /**
     * Set error handler with sentry.
     *
     * @param Container $container
     *
     * @return callable
     */
    protected function setErrorHandler(Container $container)
    {
        return function (Container $container) {
            return function (ServerRequestInterface $request, ResponseInterface $response, Exception $e) use ($container) {
                if ($container->has('appErrorHandler')) {
                    return $container['appErrorHandler']->error500($request, $response, $e);
                }

                return $response->withStatus(500);
            };
        };
    }
}

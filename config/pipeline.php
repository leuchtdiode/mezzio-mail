<?php

declare(strict_types=1);

use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Psr\Container\ContainerInterface;

/**
 * Setup middleware pipeline:
 */

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void
{
	// The error handler should be the first (most outer) middleware to catch
	// all Exceptions.
	$app->pipe(ErrorHandler::class);
	/** @var ErrorHandler $errorHandler */
	$container->get(ErrorHandler::class);
	$app->pipe(RouteMiddleware::class);
	$app->pipe(ImplicitHeadMiddleware::class);
	$app->pipe(ImplicitOptionsMiddleware::class);
	$app->pipe(MethodNotAllowedMiddleware::class);
	$app->pipe(DispatchMiddleware::class);
	$app->pipe(NotFoundHandler::class);
};

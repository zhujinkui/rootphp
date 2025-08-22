<?php

declare(strict_types=1);

namespace Zhujinkui\Rootphp\Middleware;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zhujinkui\Rootphp\Traits\ResponseData;
use Zhujinkui\Rootphp\Traits\Tool;

class SignMiddleware implements MiddlewareInterface
{
    use ResponseData;
    use Tool;

    #[Inject]
    private ConfigInterface $config;

    #[Inject]
    protected RequestInterface $http_request;

    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function __construct(protected ConfigInterface $container)
    {
        echo '中间件' . PHP_EOL;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}

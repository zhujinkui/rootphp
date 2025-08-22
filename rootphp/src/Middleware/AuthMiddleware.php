<?php

declare(strict_types=1);

namespace Zhujinkui\Rootphp\Middleware;

use Zhujinkui\Rootphp\Constants\ErrorCode;
use Zhujinkui\Rootphp\Exception\ErrorException;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qbhy\HyperfAuth\AuthManager;
use Zhujinkui\Auth\Auth;

class AuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected Auth $auth;

    #[Inject]
    protected AuthManager $jwt_manager;

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 检测Token
        if (!$request->hasHeader('token')) throw new ErrorException(ErrorCode::TOKEN_NOT_FOUND);
        // 获取Token
        $token = $request->getHeaderLine('token');
        // 获取用户ID
        $uuid = $this->jwt_manager->id($token);
        // 获取请求路径
        $action_url = $request->getUri()->getPath();
        // 验证地址权限
        $is_auth = $this->auth->checkAuth($action_url, $uuid);

        if (!$is_auth) {
            throw new ErrorException(ErrorCode::NOT_AUTHORIZED);
        }

        return $handler->handle($request);
    }
}

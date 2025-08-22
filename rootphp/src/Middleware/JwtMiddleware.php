<?php

declare(strict_types=1);

namespace Zhujinkui\Rootphp\Middleware;

use Zhujinkui\Rootphp\Constants\ErrorCode;
use Zhujinkui\Rootphp\Exception\ErrorException;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qbhy\HyperfAuth\AuthManager;

class JwtMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected AuthManager $jwt_manager;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 检测Token
        if (!$request->hasHeader('token')) throw new ErrorException(ErrorCode::TOKEN_NOT_FOUND);
        // 获取Token
        $token = $request->getHeaderLine('token');

        // 验证Token
        $check_result = $this->jwt_manager->check($token);

        if (!$check_result) throw new ErrorException(ErrorCode::TOKEN_INVALID);
        // 解析Token数据
        $member_data = $this->jwt_manager->guard('jwt')->user($token);

        Context::set('member_data', $member_data);
        Context::set('uuid', $member_data->getId());

        return $handler->handle($request);
    }
}

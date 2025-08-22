<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Zhujinkui\Rootphp\Exception\Handler;

use Zhujinkui\Rootphp\Constants\ErrorCode;
use Zhujinkui\Rootphp\Exception\ErrorException;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Qbhy\HyperfAuth\Exception\AuthException;
use Qbhy\SimpleJwt\Exceptions\JWTException;
use Throwable;
use Exception;

use function Hyperf\Support\env;

class AppExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected StdoutLoggerInterface $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('异常代码：%s', $throwable->getCode()));
        $this->logger->error(sprintf('异常描述：%s', $throwable->getMessage()));
        $this->logger->error(sprintf('异常行号：%s', $throwable->getLine()));
        $this->logger->error(sprintf('异常文件：%s', $throwable->getFile()));
        $this->logger->error(sprintf('异常流程：%s', $throwable->getTraceAsString()));

        $this->stopPropagation();

        if ($throwable instanceof NotFoundHttpException) {
            // 格式化输出
            $code      = ErrorCode::NOT_FOUND;
            $http_code = ErrorCode::getHttpCode($code);
            $message   = ErrorCode::getMessage($code);
        } elseif ($throwable instanceof ErrorException) {
            // 格式化输出
            $code      = $throwable->getCode();
            $http_code = ErrorCode::getHttpCode($code);
            $message   = $throwable->getMessage();
        } elseif ($throwable instanceof AuthException || $throwable instanceof JWTException) {
            // 格式化输出
            $code      = $throwable->getCode();
            $http_code = ErrorCode::getHttpCode($code);
            $message   = $throwable->getMessage();
        } elseif ($throwable instanceof ValidationException) {
            // 格式化输出
            $code      = ErrorCode::FAIL;
            $http_code = ErrorCode::getHttpCode($code);
            $message   = $throwable->validator->errors()->first();
        } elseif ($throwable instanceof Exception) {
            // 格式化输出
            $code      = ErrorCode::SERVER_ERROR;
            $http_code = ErrorCode::getHttpCode($code);
            $message   = $throwable->getMessage();
        } else {
            // 格式化输出
            $code      = ErrorCode::SERVER_ERROR;
            $http_code = ErrorCode::getHttpCode($code);
            $message   = $throwable->getMessage() ? $throwable->getMessage() : ErrorCode::getMessage(ErrorCode::SERVER_ERROR);
        }

        /**
         * 调试显示
         */
        $data = [
            'line' => $throwable->getLine(),
            'file' => $throwable->getFile(),
        ];

        // 格式化输出
        $json_data = json_encode([
            'code'    => $code,
            'message' => $message,
            'data'    => env('APP_ENV') == 'dev' ? $data : [],
        ], JSON_UNESCAPED_UNICODE);

        return $response->withHeader('Server', 'MingYangZhiYuan')
            ->withAddedHeader('Content-Type', 'application/json;charset=utf-8')
            ->withStatus($http_code)
            ->withBody(new SwooleStream($json_data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace Zhujinkui\Rootphp\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("成功")
     */
    const SUCCESS = 200;

    /**
     * @Message("Token失效")
     */
    const TOKEN_INVALID = 201;

    /**
     * @Message("用户不存在")
     */
    const ACCOUNT_NOT_EXIST = 1;

    /**
     * @Message("账户已禁用")
     */
    const ACCOUNT_FREEZE = 2;

    /**
     * @Message("用户或密码错误")
     */
    const LOGIN_FAILED = 3;

    /**
     * @Message("失败")
     */
    const FAIL = 400;

    /**
     * @Message("Header头缺少Token参数")
     */
    const TOKEN_NOT_FOUND = 4000;

    /**
     * @Message("暂未授权,请联系管理员")
     */
    const NOT_AUTHORIZED = 401;

    /**
     * @Message("Not Found")
     */
    const NOT_FOUND = 404;

    /**
     * @Message("Server Error！")
     */
    const SERVER_ERROR = 500;

}

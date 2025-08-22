<?php

namespace Zhujinkui\Rootphp\Traits;

use Hyperf\DbConnection\Db;

trait Tool
{
    /**
     * 验证签名
     *
     * @param array  $client_sign_data
     * @param string $sign
     *
     * @return bool
     */
    protected function verifySign(array $client_sign_data, string $sign = ''): bool
    {
        // 获取签名
        $client_sign = $this->makeSign($client_sign_data);

        if ($client_sign == $sign) {
            return true;
        }

        return false;
    }

    /**
     * 创建签名
     *
     * @param array $client_sign_data
     *
     * @return string
     */
    protected function makeSign(array $client_sign_data = []): string
    {
        //log_file($client_sign_data, '旧签名');
        // 按照字典排序
        ksort($client_sign_data);
        //log_file($client_sign_data, '新签名');

        $client_sign_str = '';

        foreach ($client_sign_data as $key => $value) {
            $client_sign_str .= $key . '=' . $value . '&';
        }

        return md5(rtrim($client_sign_str, '&'));
    }

    /**
     * 调试使用，拼接字符串
     *
     * @param array $client_sign_data
     *
     * @return string
     */
    protected function splitJointQuery(array $client_sign_data = []): string
    {
        ksort($client_sign_data);

        $client_sign_str = '';

        foreach ($client_sign_data as $key => $value) {
            $client_sign_str .= $key . '=' . $value . '&';
        }

        return rtrim($client_sign_str, '&');
    }

    /**
     * 调试使用，排序
     *
     * @param array $client_sign_data
     *
     * @return array
     */
    protected function ksortClignData(array $client_sign_data = []): array
    {
        ksort($client_sign_data);
        return $client_sign_data;
    }
}
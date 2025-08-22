<?php

namespace Zhujinkui\Rootphp\Traits;

trait ResponseData
{
    /**
     *
     * @param string $message
     * @param        $data
     *
     * @return array
     */
    function requestJson(string $message = 'success', $data = null): array
    {
        return [
            'code' => 200,
            'msg'  => $message,
            'data' => $data ?: [],
        ];
    }
}
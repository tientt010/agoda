<?php

namespace Core;

/**
 * Base Middleware class
 * Tất cả middleware sẽ kế thừa từ class này
 */
abstract class Middleware
{
    /**
     * Xử lý middleware
     * @param callable $next Callback tới middleware tiếp theo
     * @return mixed
     */
    abstract public function handle($next);
}

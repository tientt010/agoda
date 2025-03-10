<?php

namespace Core;

class ErrorHandler
{
    /**
     * Khởi tạo error handler
     */
    public static function init()
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Xử lý PHP errors
     */
    public static function handleError($level, $message, $file, $line)
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Xử lý exceptions
     */
    public static function handleException($e)
    {
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();

        // Log error
        error_log("Exception: $message in $file on line $line\n$trace");

        if (DEBUG_MODE) {
            echo "<h1>Error</h1>";
            echo "<p>$message</p>";
            echo "<p>in $file on line $line</p>";
            echo "<pre>$trace</pre>";
        } else {
            include 'app/views/errors/500.php';
        }
    }

    /**
     * Xử lý fatal errors
     */
    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null && $error['type'] === E_ERROR) {
            self::handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }
}

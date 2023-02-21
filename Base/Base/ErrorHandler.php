<?php

namespace Base\Base;

class ErrorHandler
{
    // Закрытый конструктор, не позволяющий непосредственно
    // создавать объекты класса
    private function __construct()
    {
    }

    /* Выбираем метод ErrorHandler::Handler в качестве метода обработки ошибок */
    public static function SetHandler($errTypes = E_ALL)
    {
        return set_error_handler(array('\Base\Base\ErrorHandler', 'Handler'), $errTypes);
    }

    // Метод обработки ошибок
    public static function Handler($errNo, $errStr, $errFile, $errLine)
    {
        /* Первые два элемента массива трассировки :
        - ErrorHandler.GetBacktrace
        - ErrorHandler.Handler */
        $backtrace = ErrorHandler::GetBacktrace(2);

        // Сообщения об ошибках, которые будут выводиться, отправляться по
        // электронной почте или записываться в журнал
        $error_message = "\r\nОшибка: $errNo" . "\r\nТекст ошибки: $errStr" . "\r\nФайл: $errFile, Строка " .
            "$errLine, at " . date('F j, Y, g:i a') . "\r\nСтек:\r\n$backtrace\r\n\r\n";

        // Отправляем сообщения об ошибках, если \Base::app()->config('SEND_ERROR_MAIL') равно true
        if (\Base::app()->config('SEND_ERROR_MAIL') == true)
            error_log($error_message, 1, \Base::app()->config('ADMIN_ERROR_MAIL'), "From: " . \Base::app()->config('SENDMAIL_FROM') . "\r\nTo: " .
                \Base::app()->config('ADMIN_ERROR_MAIL'));

        // Записываем сообщения в журнал, если \Base::app()->config('LOG_ERRORS') равно true
        if (\Base::app()->config('LOG_ERRORS') == true)
            error_log($error_message . PHP_EOL, 3, \Base::app()->config('LOG_ERRORS_FILE'));

        /* Выполнение не прекращается при предупреждениях,
        если \Base::app()->config('IS_WARNING_FATAL') равно false. Ошибки E_NOTICE и E_USER_NOTICE
        тоже не приводят к прекращению выполнения */
        if (($errNo == E_WARNING && \Base::app()->config('IS_WARNING_FATAL') == false) || ($errNo == E_NOTICE ||
            $errNo == E_USER_NOTICE)) {
            // Если ошибка не фатальная...
            // Выводим сообщение, только если \Base::app()->config('DEBUGGING') равно true
            if (\Base::app()->config('DEBUGGING') == true)
                echo '<meta charset="utf-8"><div class="error_box"><pre>' . $error_message . '</pre></div>';
        } else {
            // Если ошибка фатальная...
            // Выводим сообщение об ошибке
            if (\Base::app()->config('DEBUGGING') == true)
                echo '<meta charset="utf-8"><div class="error_box"><pre>' . $error_message . '</pre></div>';
            else {
                // Очищаем буфер вывода
                ob_clean();

                if (file_exists(\Base::app()->config('SITE_ROOT') . "/Base/view/500.php")) {
                    // Загружаем страницу с сообщением об ошибке 500
                    include \Base::app()->config('SITE_ROOT') . "/Base/view/500.php";
                } else {
                   header_sent('HTTP/1.0 500 Internal Server Error');
                    echo '500 Internal Server Error';
                }

                // Очищаем буфер вывода и останавливаем выполнение
                flush();
                ob_flush();
                ob_end_clean();
                exit();
            }

            // Stop processing the request
            exit();
        }
    }

    // Составляем список вызовов
    public static function GetBacktrace($irrelevantFirstEntries)
    {
        $s = '';
        $MAXSTRLEN = 64;
        $trace_array = debug_backtrace();

        for ($i = 0; $i < $irrelevantFirstEntries; $i++)
            array_shift($trace_array);
        $tabs = sizeof($trace_array) - 1;

        foreach ($trace_array as $arr) {
            $tabs -= 1;
            if (isset($arr['class']))
                $s .= $arr['class'] . '.';
            $args = array();
            if (!empty($arr['args']))
                foreach ($arr['args'] as $v) {
                    if (is_null($v))
                        $args[] = 'null';
                    elseif (is_array($v))
                        $args[] = 'Array[' . sizeof($v) . ']';
                    elseif (is_object($v))
                        $args[] = 'Object: ' . get_class($v);
                    elseif (is_bool($v))
                        $args[] = $v ? 'true' : 'false';
                    else {
                        $v = (string )@$v;
                        $str = htmlspecialchars(substr($v, 0, $MAXSTRLEN), ENT_QUOTES, 'UTF-8');
                        if (strlen($v) > $MAXSTRLEN)
                            $str .= '...';
                        $args[] = '"' . $str . '"';
                    }
                }
            $s .= $arr['function'] . '(' . implode(', ', $args) . ')';
            $line = (isset($arr['line']) ? $arr['line'] : 'unknown');
            $file = (isset($arr['file']) ? $arr['file'] : 'unknown');
            $s .= sprintf(' # line %4d, file: %s', $line, $file);
            $s .= "\r\n";
        }
        return $s;
    }
}

?>
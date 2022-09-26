<?php

use Frizus\Module\Helper\ViewBuffer;

function view_buffer($key, $callback = null, $variables = null)
{
    if (func_num_args() === 1) {
        if (array_key_exists($key, ViewBuffer::$buffer)) {
            $buffer = ViewBuffer::$buffer[$key];
            unset(ViewBuffer::$buffer[$key]);
            return $buffer;
        }
        return null;
    } else {
        ob_start();
        if (is_callable($callback)) {
            $callback();
        } else {
            view($callback, $variables);
        }
        ViewBuffer::$buffer[$key] = ob_get_clean();
    }
}

function view($path, $variables = null)
{
    if (isset($variables)) {
        extract($variables, EXTR_SKIP);
    }
    require VIEWS_PATH . $path . '.php';
}

function view_once($path, $variables = null)
{
    if (isset($variables)) {
        extract($variables, EXTR_SKIP);
    }
    require_once VIEWS_PATH . $path . '.php';
}

function widget($name)
{
    /** @var Frizus\Module\Widget\Widget $widgetClass */
    $widgetClass = "\\Frizus\\Module\\Widget\\" . $name . "Widget";
    $widgetClass::run();
}

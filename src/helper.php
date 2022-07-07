<?php

use Webman\Event\Event;

if (!function_exists('event')) {
    function event($event_name, $data = null, $once = false)
    {
        return Event::emit($event_name, $data, $once);
    }
}

if (!function_exists('has_listener')) {
    function has_listener($event_name)
    {
        return Event::hasListener($event_name);
    }
}

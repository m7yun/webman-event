<?php

namespace Webman\Event;

use Psr\Log\LoggerInterface;
use support\Log;

class Event
{
    /**
     * @var array
     */
    protected static $eventMap = [];

    /**
     * @var array
     */
    protected static $prefixEventMap = [];

    /**
     * @var int
     */
    protected static $id = 0;

    /**
     * @var LoggerInterface
     */
    protected static $logger;

    /**
     * @param $event_name
     * @param $callback
     * @return int
     */
    public static function on($event_name, callable $callback): int
    {
        $is_prefix_name = $event_name[strlen($event_name) - 1] === '*';
        if ($is_prefix_name) {
            static::$prefixEventMap[substr($event_name, 0, -1)][++static::$id] = $callback;
        } else {
            static::$eventMap[$event_name][++static::$id] = $callback;
        }
        return static::$id;
    }

    /**
     * @param $event_name
     * @param $id
     * @return int
     */
    public static function off($event_name, int $id): int
    {
        if (isset(static::$eventMap[$event_name][$id])) {
            unset(static::$eventMap[$event_name][$id]);
            return 1;
        }
        return 0;
    }

    /**
     * 是否存在监听
     *
     * @param $event_name
     * @return bool
     */
    public static function hasListener($event_name): bool
    {
        $callbacks = static::$eventMap[$event_name] ?? [];
        return count($callbacks) > 0;
    }

    /**
     * @param $event_name
     * @param $data
     * @param bool $once 只获取一个有效返回值
     * @return mixed
     */
    public static function emit($event_name, $data = null, bool $once = false)
    {
        $result = [];
        $callbacks = static::$eventMap[$event_name] ?? [];
        foreach (static::$prefixEventMap as $name => $callback_items) {
            if (strpos($event_name, $name) === 0) {
                $callbacks = array_merge($callbacks, $callback_items);
            }
        }
        ksort($callbacks);
        foreach ($callbacks as $key => $callback) {
            $result[$key] = $callback($data, $event_name);
            if (false === $result[$key] || (!is_null($result[$key]) && $once)) {
                break;
            }
        }
        return $once ? end($result) : $result;
    }

    /**
     * @return array
     */
    public static function list(): array
    {
        $callbacks = [];
        foreach (static::$eventMap as $event_name => $callback_items) {
            foreach ($callback_items as $id => $callback_item) {
                $callbacks[$id] = [$event_name, $callback_item];
            }
        }
        foreach (static::$prefixEventMap as $event_name => $callback_items) {
            foreach ($callback_items as $id => $callback_item) {
                $callbacks[$id] = [$event_name . '*', $callback_item];
            }
        }
        ksort($callbacks);
        return $callbacks;
    }
}

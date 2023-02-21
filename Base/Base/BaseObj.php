<?php

namespace Base\Base;

if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
    class BaseObj implements \Iterator
    {
        public $parentObj;
        protected $vars = [];
        protected $events = [];

        public function __construct($vars = null)
        {
            if (is_array($vars)) {
                $this->vars = $vars;
            } else {
                $this->vars = [];
            }
        }

        public function __isset($key)
        {
            if (array_key_exists($key, $this->vars)) {
                return true;
            }

            return false;
        }

        public function __get($key)
        {
            if (isset($this->vars[$key])) {
                return $this->vars[$key];
            }
            return null;
        }

        public function __set($key, $val)
        {
            $this->vars[$key] = $val;

            if (isset($this->events['set'])) {
                $this->events['set']($this, $key, $val);
            }
        }

        public function first()
        {
            $key = array_key_first($this->vars);

            if ($key) {
                return $this->vars[$key];
            }

            return null;
        }

        public function event($event, $function)
        {
            if (is_callable($function)) {
                $this->events[$event] = $function;
            }
        }

        public function __unset($key)
        {
            if (isset($this->vars[$key])) {
                unset($this->vars[$key]);
            }
        }

        public function count()
        {
            return count($this->vars);
        }

        public function all()
        {
            return $this->vars;
        }

        public function rewind() : void
        {
            reset($this->vars);
        }

        public function current() : mixed
        {
            return current($this->vars);
        }

        public function key() : mixed
        {
            return key($this->vars);
        }

        public function next() : void
        {
            next($this->vars);
        }

        public function valid() : bool
        {
            $key = key($this->vars);

            $var = ($key !== null && $key !== false);

            return $var;
        }

        public function uasort($callback)
        {
            if (is_callable($callback)) {
                uasort($this->vars, $callback);
            }
        }
    }
} else {
    class BaseObj implements \Iterator
    {
        public $parentObj;
        protected $vars = [];
        protected $events = [];

        public function __construct($vars = null)
        {
            if (is_array($vars)) {
                $this->vars = $vars;
            } else {
                $this->vars = [];
            }
        }

        public function __isset($key)
        {
            if (array_key_exists($key, $this->vars)) {
                return true;
            }

            return false;
        }

        public function __get($key)
        {
            if (isset($this->vars[$key])) {
                return $this->vars[$key];
            }
            return null;
        }

        public function __set($key, $val)
        {
            $this->vars[$key] = $val;

            if (isset($this->events['set'])) {
                $this->events['set']($this, $key, $val);
            }
        }

        public function first()
        {
            $key = array_key_first($this->vars);

            if ($key) {
                return $this->vars[$key];
            }

            return null;
        }

        public function event($event, $function)
        {
            if (is_callable($function)) {
                $this->events[$event] = $function;
            }
        }

        public function __unset($key)
        {
            if (isset($this->vars[$key])) {
                unset($this->vars[$key]);
            }
        }

        public function count()
        {
            return count($this->vars);
        }

        public function all()
        {
            return $this->vars;
        }

        public function rewind()
        {
            reset($this->vars);
        }

        public function current()
        {
            return current($this->vars);
        }

        public function key()
        {
            return key($this->vars);
        }

        public function next()
        {
            next($this->vars);
        }

        public function valid()
        {
            $key = key($this->vars);

            $var = ($key !== null && $key !== false);

            return $var;
        }

        public function uasort($callback)
        {
            if (is_callable($callback)) {
                uasort($this->vars, $callback);
            }
        }
    }
}

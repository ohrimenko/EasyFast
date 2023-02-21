<?php

namespace Base\Record;

use Base\Base\DB;

if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
    abstract class Collection
    {
        protected $dofact;
        protected $total = 0;
        protected $raw = array();

        private $result;
        private $pointer = 0;
        private $objects = array();

        function __construct(array $raw = null, ModelObjectFactory $dofact = null)
        {
            if (!is_null($raw) && !is_null($dofact)) {
                $this->raw = $raw;
                $this->total = count($raw);
            }
            $this->dofact = $dofact;
        }

        function count()
        {
            return $this->total;
        }

        function add(\Base\model\ModelObject $object)
        {
            $class = $this->targetClass();
            if (!($object instanceof $class)) {
                throw new Exception("This is a {$class} collection");
            }
            $this->notifyAccess();
            $this->objects[$this->total] = $object;
            $this->total++;
        }

        function getGenerator()
        {
            for ($x = 0; $x < $this->total; $x++) {
                yield($this->getRow($x));
            }
        }

        abstract function targetClass();

        protected function notifyAccess()
        {
            // deliberately left blank!
        }

        protected function getRow($num)
        {
            $this->notifyAccess();
            if ($num >= $this->total || $num < 0) {
                return null;
            }
            if (isset($this->objects[$num])) {
                return $this->objects[$num];
            }

            if (isset($this->raw[$num])) {
                $this->objects[$num] = $this->dofact->createObject($this->raw[$num]);
                return $this->objects[$num];
            }
        }

        public function rewind() : void
        {
            $this->pointer = 0;
        }

        public function current() : mixed
        {
            return $this->getRow($this->pointer);
        }

        public function key() : mixed
        {
            return $this->pointer;
        }

        public function next() : void
        {
            $row = $this->getRow($this->pointer);

            if ($row) {
                $this->pointer++;
            }
        }

        public function valid() : bool
        {
            return (!is_null($this->current()));
        }
    }
} else {
    abstract class Collection
    {
        protected $dofact;
        protected $total = 0;
        protected $raw = array();

        private $result;
        private $pointer = 0;
        private $objects = array();

        function __construct(array $raw = null, ModelObjectFactory $dofact = null)
        {
            if (!is_null($raw) && !is_null($dofact)) {
                $this->raw = $raw;
                $this->total = count($raw);
            }
            $this->dofact = $dofact;
        }

        function count()
        {
            return $this->total;
        }

        function add(\Base\model\ModelObject $object)
        {
            $class = $this->targetClass();
            if (!($object instanceof $class)) {
                throw new Exception("This is a {$class} collection");
            }
            $this->notifyAccess();
            $this->objects[$this->total] = $object;
            $this->total++;
        }

        function getGenerator()
        {
            for ($x = 0; $x < $this->total; $x++) {
                yield($this->getRow($x));
            }
        }

        abstract function targetClass();

        protected function notifyAccess()
        {
            // deliberately left blank!
        }

        protected function getRow($num)
        {
            $this->notifyAccess();
            if ($num >= $this->total || $num < 0) {
                return null;
            }
            if (isset($this->objects[$num])) {
                return $this->objects[$num];
            }

            if (isset($this->raw[$num])) {
                $this->objects[$num] = $this->dofact->createObject($this->raw[$num]);
                return $this->objects[$num];
            }
        }

        public function rewind()
        {
            $this->pointer = 0;
        }

        public function current()
        {
            return $this->getRow($this->pointer);
        }

        public function key()
        {
            return $this->pointer;
        }

        public function next()
        {
            $row = $this->getRow($this->pointer);

            if ($row) {
                $this->pointer++;
            }
        }

        public function valid()
        {
            return (!is_null($this->current()));
        }
    }
}

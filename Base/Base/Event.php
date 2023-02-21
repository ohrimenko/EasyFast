<?php

namespace Base\Base;

class Event
{
    protected $listens = [];

    public function listen($event, $handle)
    {
        if ($event && is_callable($handle)) {
            $this->listens[$event][] = $handle;
        }
    }

    public function event($event, $data = null, $handle = null)
    {
        if ($event) {
            if (isset($this->listens[$event])) {
                foreach ($this->listens[$event] as $listen) {
                    if (($response = ($listen($data))) === false) {
                        continue;
                    } elseif ($handle) {
                        $handle($response);
                    }
                }
                unset($this->listens[$event]);
            }
        }
    }
}

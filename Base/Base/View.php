<?php

namespace Base\Base;

use \Base;
use \Base\Base\Data;
use \Base\Base\BaseView;

class View extends BaseView
{
    private $areas = [];
    private $areas_stack = [];
    private $stack = [];
    private $act = null;
    private $callbacks = [];

    private $view;
    private $data;

    private $path;

    protected $i_start = 0;
    protected $i_end = 0;

    use IncludeFile;

    public function __construct($view, $data = null)
    {
        $this->view = ltrim($view, '/');

        if ($data instanceof Data) {
            $this->data = $data;
        } elseif (is_array($data)) {
            $this->data = new Data($data);
        } else {
            $this->data = new Data();
        }

        $this->path = \Base::app()->config('SITE_ROOT') . DIRECTORY_SEPARATOR . 'view';
    }

    public function render($return = false)
    {
        $out = '';
        
        ob_start();
        
        try {
            $this->show('main', 'append');

            $this->view($this->view);

            $this->end();
            
            foreach ($this->callbacks as $callback) {
                $callback($this);
            }

            //var_dump($this->stack);
            //var_dump(self::$block_stack);
            //var_dump($this->areas_stack);
            //var_dump(self::$stack_buffer);
            //var_dump(self::$stack_include);
            //var_dump($this->stack_buffer_block);
            //var_dump(self::$active_includes);

            //$this->flushStackBufferBlock();

            $this->flushCacheView();

            //$this->flushBlocksView();

            $this->flush_stack_buffer();

            $out .= $this->out($this->stack);

            self::ob_end_flush();

            if ($this->i_start != $this->i_end) {
                trigger_error('Не закрыта секция.', E_USER_ERROR);
                exit;
            }
        }
        catch (\Exception $e) {
            $this->flush_buffer('append');

            trigger_error($e->getMessage(), E_USER_ERROR);
            exit;
        }
        
        $out .= ob_get_contents();
        
        if ($return) {
            return $out;
        } else {
            echo $out;
        }
    }

    protected function flushBlocksView()
    {
        foreach (self::$blocks_view as $block) {
            foreach (self::$block_stack as $areas) {
                if ($areas[count($areas) - 1] == $block['area']) {
                    self::$stack_buffer[] = ['areas' => $areas, 'out' => $block['out'], 'act' => $block['act'],
                        'type' => 'out'];

                    continue;
                }
            }

        }
    }

    protected function flushCacheView()
    {
        foreach (self::$stack_include as $key => $value) {
            if ($this->cacheConfig($key)) {
                $cache = ['stack_buffer' => [], 'block_stack' => [], ];

                foreach (self::$stack_include[$key]['buffer'] as $i) {
                    if (isset(self::$stack_buffer[$i]))
                        $cache['stack_buffer'][] = self::$stack_buffer[$i];
                }
                foreach (self::$stack_include[$key]['blocks'] as $i) {
                    if (isset(self::$block_stack[$i]))
                        $cache['block_stack'][] = self::$block_stack[$i];
                }

                $this->cache($key, $cache, $this->cacheConfig($key));
            }
        }
    }

    public function show($area)
    {
        $this->flush_buffer('append');

        $this->start($area, 'append');

        $this->createSection();
    }

    public function section($area)
    {
        $this->flush_buffer('append');

        $this->search_area($area, 'replace');
    }

    public function delete($area)
    {
        foreach (self::$block_stack as $k => $areas) {
            if ($areas[count($areas) - 1] == $area) {
                foreach (self::$stack_buffer as $key => $buffer) {
                    if ($buffer['areas'] == $areas) {
                        self::$stack_buffer[$key] = ['act' => 'append', 'areas' => [''], 'out' => '',
                            'type' => 'out'];
                        //unset(self::$stack_buffer[$key]);
                    }
                }

                self::$block_stack[$k] = [''];
            }
        }
    }

    public function append($area)
    {
        $this->flush_buffer('append');

        $this->search_area($area, 'append');
    }

    public function prepend($area)
    {
        $this->flush_buffer('prepend');

        $this->search_area($area, 'prepend');
    }

    public function block($area)
    {
        $this->delete($area);

        $this->show($area);

        $this->end();
    }

    protected function createSection()
    {
        $this->set_areas_stack();

        if (!in_array($this->areas, self::$block_stack)) {
            self::$block_stack[++self::$i_block] = $this->areas;

            self::$stack_buffer[++self::$i_buffer] = ['areas' => $this->areas, 'type' =>
                'section'];

            foreach (self::$active_includes as $include_key) {
                self::$stack_include[$include_key]['buffer'][] = self::$i_buffer;
            }

            foreach (self::$active_includes as $include_key) {
                self::$stack_include[$include_key]['blocks'][] = self::$i_block;
            }
        }
    }

    protected function flushStackBufferBlock()
    {
        foreach (self::$block_stack as $areas) {
            array_set($this->stack, $areas, [], false);
        }
    }

    protected function search_area($area, $act)
    {
        foreach (self::$block_stack as $areas) {
            if ($areas[array_key_last($areas)] == $area) {
                $this->areas = $areas;
                $this->act = $act;

                $this->set_areas_stack();

                $this->i_start++;

                return;
            }
        }

        $this->start($area, $act);
    }

    protected function set_areas_stack()
    {
        $this->areas_stack[] = $this->areas;
    }

    protected function start($area, $act)
    {
        $this->areas[] = $area;
        $this->act = $act;

        $this->i_start++;
    }

    protected function flush_buffer($act = null)
    {
        $out = ob_get_contents();
        ob_clean();

        $out = trim($out, "\r\n");

        if ($out === false || $out === '') {
            return;
        }

        if (empty($this->areas)) {
            $this->areas = ['main'];
        }

        self::$stack_buffer[++self::$i_buffer] = ['act' => $act ? $act : $this->act,
            'areas' => $this->areas, 'out' => $out, 'type' => 'out'];

        foreach (self::$active_includes as $include_key) {
            self::$stack_include[$include_key]['buffer'][] = self::$i_buffer;
        }
    }

    protected function flush_stack_buffer()
    {
        foreach (self::$stack_buffer as $key => $buffer) {
            switch ($buffer['type']) {
                case 'out':

                    switch ($buffer['act']) {
                        case 'show':
                            array_set($this->stack, $buffer['areas'], $key, false);

                            break;
                        case 'replace':
                            array_replaces($this->stack, $buffer['areas'], [$key], false);

                            break;
                        case 'append':
                            array_append($this->stack, $buffer['areas'], $key, false);

                            break;
                        case 'prepend':
                            array_prepend($this->stack, $buffer['areas'], $key, false);

                            break;
                    }
                    break;
                case 'section':
                    array_set($this->stack, $buffer['areas'], [], false);
                    break;
            }
        }
    }

    public function end()
    {
        $this->flush_buffer();

        if (count($this->areas_stack) > 1) {
            array_pop($this->areas_stack);
        }
        $this->areas = $this->areas_stack[count($this->areas_stack) - 1];

        $this->act = 'append';

        $this->i_end++;
    }

    protected function out($stack)
    {
        $out = '';

        if (is_array($stack)) {
            foreach ($stack as $st) {
                $out .= $this->out($st);
            }
        } else {
            $out .= self::$stack_buffer[$stack]['out'];
        }

        return $out;
    }

    protected function favicon($file)
    {
        $file = $this->fileUrl($file);
        if ($file) {
            echo '<link rel="icon" type="image/png" href="' . $file . '" />';
        }
    }

    protected function css($file)
    {
        $file = $this->fileUrl($file);
        if ($file) {
            $this->includeCss($file, '<link rel="stylesheet" href="' . $file . '" />');
        }

    }

    protected function javascript($file)
    {
        $file = $this->fileUrl($file);
        if ($file) {
            $this->includeJavascript($file, '<script type="text/javascript" src="' . $file .
                '"></script>');
        }
    }

    protected function img($file, $options = [])
    {
        $file = $this->fileUrl($file);
        if ($file) {
            $attrs = [];

            foreach ($options as $key => $value) {
                $attrs[] = str_replace(["'", '"'], '', $key) . '="' . str_replace(["'", '"'], '',
                    $value) . '"';
            }

            echo '<img src="' . $file . '" ' . implode(' ', $attrs) . '" />';
        }
    }

    protected function fileUrl($file)
    {
        return $file;
    }

    public function callback($callback)
    {
        if (is_callable($callback)) {
            $this->callbacks[] = $callback;
        }
    }

    public function path($view, $is_exists = false)
    {
        if ($is_exists) {
            $path = $this->path . DIRECTORY_SEPARATOR . app()->template() . DIRECTORY_SEPARATOR . ltrim($view, DIRECTORY_SEPARATOR);

            $pinf = pathinfo($path);

            if (!isset($pinf['extension'])) {
                $path .= '.php';
            }

            if (file_exists($path)) {
                return $path;
            } else {
                return null;
                
                $path = $this->path . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . ltrim($view, DIRECTORY_SEPARATOR);

                $pinf = pathinfo($path);

                if (!isset($pinf['extension'])) {
                    $path .= '.php';
                }

                if (file_exists($path)) {
                    return $path;
                } else {
                    return null;
                }
            }
        } else {
            $path = $this->path . DIRECTORY_SEPARATOR . app()->template() . DIRECTORY_SEPARATOR . ltrim($view, DIRECTORY_SEPARATOR);

            $pinf = pathinfo($path);

            if (!isset($pinf['extension'])) {
                $path .= '.php';
            }

            return $path;
        }
    }

    protected function view($view, $dataView = [], $key = null)
    {
        $methodView = 'view' . $view;

        $include_key = $view . ($key ? '_' . $key : '');

        for ($i = 0; isset(self::$stack_include[$include_key]) && $i < 10; $i++) {
            $include_key .= str_rand();
        }

        if (!empty($view)) {
            if ($this->cacheConfig($include_key) && $cache = $this->cache($include_key)) {
                if (is_array($cache['stack_buffer'])) {
                    foreach ($cache['stack_buffer'] as $buffer) {
                        self::$stack_buffer[++self::$i_buffer] = $buffer;
                    }
                }
                if (is_array($cache['block_stack'])) {
                    foreach ($cache['block_stack'] as $buffer) {
                        self::$block_stack[++self::$i_block] = $buffer;
                    }
                }

                //self::$stack_buffer = array_merge(self::$stack_buffer, $cache['stack_buffer']);
                //self::$block_stack = array_merge(self::$block_stack, $cache['block_stack']);
            } elseif ($path = $this->path($view, true)) {
                $this->flush_buffer();

                self::$active_includes[] = $include_key;

                self::$stack_include[$include_key]['buffer'] = [];
                self::$stack_include[$include_key]['blocks'] = [];

                $data = $this->data;

                extract($dataView);

                $this->show($view . str_rand(10));
                require ($path);
                $this->end();

                $this->flush_buffer();

                array_pop(self::$active_includes);
            } else {
                echo '<div style="color: red; font-family: cursive; font-size: 12px;">Not View: ' .
                    $view . '</div>';
            }
        } else {
            echo '<div style="color: red; font-family: cursive; font-size: 12px;">Empty View: </div>';
        }
    }

    protected function cacheConfig($key)
    {
        return Base::config('cache_view.' . $key);
    }

    protected function cache($key, $value = null, $time = 60)
    {
        if ($value) {
            return Base::cache($key, $value, $time);
        } else {
            return Base::cache($key);
        }
    }
}

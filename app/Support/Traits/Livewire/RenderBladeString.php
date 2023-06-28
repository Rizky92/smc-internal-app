<?php

namespace App\Support\Traits\Livewire;

use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\View\Component;

trait RenderBladeString
{

    public function getTemplate(string $key, ?string $default = null)
    {
        if (method_exists($this, 'stringTemplates')) {
            return $this->stringTemplates()[$key];
        }

        if (property_exists($this, 'stringTemplates')) {
            return $this->stringTemplates[$key];
        }

        return $default;
    }

    /** 
     * @param  string $key
     * @param  \Illuminate\Support\Collection|array $data
     */
    public function renderBladeString(string $key, $data, bool $deleteCachedView = false)
    {
        $template = $this->getTemplate($key);

        $component = new class($template) extends Component
        {
            protected string $template;

            public function __construct($template)
            {
                $this->template = $template;
            }

            public function render()
            {
                return $this->template;
            }
        };

        $view = Container::getInstance()
                    ->make(ViewFactory::class)
                    ->make($component->resolveView(), $data);

        return tap($view->render(), function () use ($view, $deleteCachedView) {
            if ($deleteCachedView) {
                unlink($view->getPath());
            }
        });
    }
}
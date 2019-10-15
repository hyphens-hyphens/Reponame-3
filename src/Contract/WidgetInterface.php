<?php
namespace T2G\Common\Contract;

interface WidgetInterface
{
    /**
     * @return string|\Illuminate\View\View
     */
    public function render();
}

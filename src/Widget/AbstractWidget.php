<?php

namespace T2G\Common\Widget;

use T2G\Common\Contract\WidgetInterface;

/**
 * Class AbstractWidget
 *
 * @package \T2G\Common\Widget
 */
abstract class AbstractWidget implements WidgetInterface
{
    /**
     * @return string
     */
    abstract protected function getViewPermission();

    /** string|\Illuminate\View\View */
    abstract protected function loadWidget();

    /**
     * @return bool
     * @throws \Exception
     */
    protected function canView()
    {
        return voyager()->can($this->getViewPermission());
    }

    /**
     * @return \Illuminate\View\View|string|null
     * @throws \Exception
     */
    public function render()
    {
        if (!$this->canView()) {
            return null;
        }

        return $this->loadWidget();
    }
}

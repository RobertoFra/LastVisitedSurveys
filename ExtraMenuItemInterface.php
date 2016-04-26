<?php

/**
 * Interface descriptions here: https://manual.limesurvey.org/Extra_menus_event
 * @todo Move interfaces and classes into core so other plugins can use them in other menu extention points
 */
interface ExtraMenuItemInterface
{
    public function getHref();
    public function getLabel();
    public function isDivider();
}


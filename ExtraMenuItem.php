<?php

class ExtraMenuItem implements ExtraMenuItemInterface
{

    protected $isDivider = false;
    protected $href = "#";
    protected $label = "Missing label";

    public function __construct($options)
    {
      if (isset($options['isDivider']))
      {
          $this->isDivider = $options['isDivider'];
      }

      if (isset($options['label']))
      {
          $this->label = $options['label'];
      }

      if (isset($options['href']))
      {
          $this->href = $options['href'];
      }
    }

    public function getHref() { return $this->href; }
    public function getLabel() { return $this->label; }
    public function isDivider() { return $this->isDivider; }

    // Used by array_unique
    public function __toString() { return $this->href; }
}


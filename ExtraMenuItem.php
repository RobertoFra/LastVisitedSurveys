<?php

class ExtraMenuItem implements ExtraMenuItemInterface
{

    protected $isDivider = false;
    protected $isSmallText = false;
    protected $href = "#";
    protected $label = "Missing label";

    public function __construct($options)
    {
      if (isset($options['isDivider']))
      {
          $this->isDivider = $options['isDivider'];
      }

      if (isset($options['isSmallText']))
      {
          $this->isSmallText = $options['isSmallText'];
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
    public function isSmallText() { return $this->isSmallText; }

    // Used by array_unique
    public function __toString() { return $this->href; }
}


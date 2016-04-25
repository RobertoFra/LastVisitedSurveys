<?php 

/**
 * Interface descriptions here: https://manual.limesurvey.org/Extra_menus_event
 * @todo Move interfaces and classes into core so other plugins can use them in other menu extention points
 */

interface ExtraMenuInterface
{
    public function isDropDown();
    public function getLabel();
    public function getHref();
    public function getMenuItems();
}

interface ExtraMenuItemInterface
{
    public function getHref();
    public function getLabel();
    public function isDivider();
}

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
}

class ExtraMenu implements ExtraMenuInterface
{
  protected $isDropDown = false;
  protected $label = "Missing label";
  protected $href = "#";
  protected $menuItems = array();

  /**
   * @param array $options - Options for either dropdown menu or plain link
   * @return ExtraMenu
   */
  public function __construct($options)
  {
      if (isset($options['isDropDown']))
      {
          $this->isDropDown = $options['isDropDown'];
      }

      if (isset($options['label']))
      {
          $this->label = $options['label'];
      }

      if (isset($options['href']))
      {
          $this->href = $options['href'];
      }

      if (isset($options['menuItems']))
      {
          $this->menuItems = $options['menuItems'];
      }
  }

  public function isDropDown() { return $this->isDropDown; }
  public function getLabel() { return $this->label; }
  public function getHref() { return $this->href; }
  public function getMenuItems() { return $this->menuItems; }
}

/**
 * Some extra quick-menu items to ease everyday usage
 *
 * @since 2016-04-22
 * @author Olle Härstedt
 */
class LastVisitedSurveys extends \ls\pluginmanager\PluginBase
{
    static protected $description = 'Add a top menu for the last five surveys you visited, to enhance navigation between different surveys';
    static protected $name = 'Last visited surveys';

    protected $storage = 'DbStorage';

    public function init()
    {
        $this->subscribe('beforeAdminMenuRender');
        $this->subscribe('beforeActivate');
        $this->subscribe('beforeDeactivate');
    }

    public function beforeActivate()
    {
        // Create database table to store visited surveys
    }

    public function beforeDeactivate()
    {
        // Remove table
    }

    public function beforeAdminMenuRender()
    {
        // Return new menu
        $event = $this->getEvent();
        $event->set('extraMenus', array(
            new ExtraMenu(null)
        ));
        $event->set('extraMenus', array(
          new ExtraMenu(array(
            'isDropDown' => true,
            'menuItems' => array(
                new ExtraMenuItem(null),
                new ExtraMenuItem(null),
                new ExtraMenuItem(array('isDivider' => true)),
                new ExtraMenuItem(null)
            )
          ))
        ));
    }
}

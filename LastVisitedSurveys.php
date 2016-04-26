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

    /**
     * Create database table to store last visited surveys
     *
     * @todo Uses MyISAM as engine in MySQL?
     * @return void
     */
    public function beforeActivate()
    {
        // Create database table to store visited surveys
        // Code copied from updatedb_helper.
        // TODO: Include routine in plugin system?
        $oDB = Yii::app()->getDb();
        $oDB->schemaCachingDuration=0; // Deactivate schema caching
        $oTransaction = $oDB->beginTransaction();
        try
        {
            $aFields = array(
                'uid' => 'pk',
                'sid1' => 'integer',
                'sid2' => 'integer',
                'sid3' => 'integer',
                'sid4' => 'integer',
                'sid5' => 'integer',
            );
            $oDB->createCommand()->createTable('{{plugin_last_visited_surveys}}',$aFields);
            $oDB->createCommand()->addForeignKey(
                'fk_survey_id',
                '{{plugin_last_visited_surveys}}',
                'uid',
                '{{users}}',
                'uid',
                'CASCADE',
                'CASCADE'
            );
            $oDB->createCommand()->addForeignKey(
                'fk_sid1',
                '{{plugin_last_visited_surveys}}',
                'sid1',
                '{{surveys}}',
                'sid'
            );
            $oDB->createCommand()->addForeignKey(
                'fk_sid2',
                '{{plugin_last_visited_surveys}}',
                'sid2',
                '{{surveys}}',
                'sid'
            );
            $oDB->createCommand()->addForeignKey(
                'fk_sid3',
                '{{plugin_last_visited_surveys}}',
                'sid3',
                '{{surveys}}',
                'sid'
            );
            $oDB->createCommand()->addForeignKey(
                'fk_sid4',
                '{{plugin_last_visited_surveys}}',
                'sid4',
                '{{surveys}}',
                'sid'
            );
            $oDB->createCommand()->addForeignKey(
                'fk_sid5',
                '{{plugin_last_visited_surveys}}',
                'sid5',
                '{{surveys}}',
                'sid'
            );
        }
        catch(Exception $e)
        {
            $oTransaction->rollback();
            // Activate schema caching
            $oDB->schemaCachingDuration = 3600;
            // Load all tables of the application in the schema
            $oDB->schema->getTables();
            // Clear the cache of all loaded tables
            $oDB->schema->refresh();
            $event = $this->getEvent();
            $event->set('success', false);
            $event->set(
                'message',
                gT('An non-recoverable error happened during the update. Error details:')
                . "<p>"
                . htmlspecialchars($e->getMessage())
                . "</p>"
            );
            return;
        }
    }

    public function beforeDeactivate()
    {
        // Remove table
        $oDB = Yii::app()->getDb();
        $oDB->schemaCachingDuration=0; // Deactivate schema caching
        $oTransaction = $oDB->beginTransaction();
        try
        {
            $oDB->createCommand()->dropTable('{{asdplugin_last_visited_surveys}}');
        }
        catch(Exception $e)
        {
            $oTransaction->rollback();
            // Activate schema caching
            $oDB->schemaCachingDuration = 3600;
            // Load all tables of the application in the schema
            $oDB->schema->getTables();
            // Clear the cache of all loaded tables
            $oDB->schema->refresh();
            $event = $this->getEvent();
            $event->set(
                'message',
                gT('An non-recoverable error happened during the update. Error details:')
                . "<p>"
                . htmlspecialchars($e->getMessage())
                . '</p>'
            );
            return;
        }
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

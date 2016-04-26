<?php 

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
        $this->subscribe('beforeSurveyAdminView');
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
            $oDB->createCommand()->dropTable('{{plugin_last_visited_surveys}}');
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

    public function beforeSurveyAdminView()
    {
        // Get row from database
        $userId = Yii::app()->user->getId();
        $lastVisitedSurveys = LastVisitedSurveysModel::model()->findByPk($userId);

        if ($lastVisitedSurveys === null)
        {
            // First usage after plugin activation
            $lastVisitedSurveys = new LastVisitedSurveysModel();
            $lastVisitedSurveys->uid = $userId;
            $lastVisitedSurveys->save();
        }

        // Check if visited survey is already in queue. Then do nothing.
        // TODO: Move it to top instead
        $event = $this->getEvent();
        $surveyId = $event->get('surveyId');
        $surveyAlreadyInList = $this->surveyAlreadyInList($surveyId, $lastVisitedSurveys);
        if ($surveyAlreadyInList)
        {
            return;
        }

        // Push all last visited surveys back in queue
        $lastVisitedSurveys->sid5 = $lastVisitedSurveys->sid4;
        $lastVisitedSurveys->sid4 = $lastVisitedSurveys->sid3;
        $lastVisitedSurveys->sid3 = $lastVisitedSurveys->sid2;
        $lastVisitedSurveys->sid2 = $lastVisitedSurveys->sid1;
        $lastVisitedSurveys->sid1 = $surveyId;
        $lastVisitedSurveys->save();
    }

    /**
     * @param int $surveyId
     * @param LastVisitedSurveysModel $lastVisitedSurveys
     * @return bool
     */
    protected function surveyAlreadyInList($surveyId, LastVisitedSurveysModel $lastVisitedSurveys)
    {
       return $surveyId == $lastVisitedSurveys->sid1
        || $surveyId == $lastVisitedSurveys->sid2
        || $surveyId == $lastVisitedSurveys->sid3
        || $surveyId == $lastVisitedSurveys->sid4
        || $surveyId == $lastVisitedSurveys->sid5;
    }

    protected function getMenuItems($lastVisitedSurveys) {
        $menuItems = array();

        if ($lastVisitedSurveys === null)
        {
            return $menuItems;
        }

        $sids = array('sid1', 'sid2', 'sid3', 'sid4', 'sid5');

        // TODO: Use only survey1, not sid etc
        $surveys = array('survey1', 'survey2', 'survey3', 'survey4', 'survey5');

        foreach ($sids as $i => $sid)
        {
            $surveyVariable = $surveys[$i];
            $survey = $lastVisitedSurveys->$surveyVariable;
            if ($survey !== null)
            {
                $surveyInfo = $survey->surveyInfo;
                $menuItems[$i] = new ExtraMenuItem(array(
                    'label' => $surveyInfo['surveyls_title'],
                    'href' => Yii::app()->createUrl('/admin/survey/sa/view/surveyid/' . $lastVisitedSurveys->$sid)
                ));
            }
        }

        $menuItems = array_unique($menuItems);

        return $menuItems;
    }

    public function beforeAdminMenuRender()
    {
        // Get row from database
        $userId = Yii::app()->user->getId();
        $lastVisitedSurveys = LastVisitedSurveysModel::model()->findByPk($userId);

        $menuItems = $this->getMenuItems($lastVisitedSurveys);
        // Return new menu
        $event = $this->getEvent();
        $event->set('extraMenus', array(
          new ExtraMenu(array(
            'isDropDown' => true,
            'label' => gT('Recently visited'),
            'menuItems' => $menuItems
          ))
        ));
    }
}

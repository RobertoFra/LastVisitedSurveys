<?php

class LastVisitedSurveysModel extends LSActiveRecord
{
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    public function tableName()
    {
        return '{{plugin_last_visited_surveys}}';
    }

    public function primaryKey()
    {
        return array('uid');
    }

    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'uid'),
            'survey1' => array(self::BELONGS_TO, 'Survey', 'sid1'),
            'survey2' => array(self::BELONGS_TO, 'Survey', 'sid2'),
            'survey3' => array(self::BELONGS_TO, 'Survey', 'sid3'),
            'survey4' => array(self::BELONGS_TO, 'Survey', 'sid4'),
            'survey5' => array(self::BELONGS_TO, 'Survey', 'sid5'),
        );
    }
}


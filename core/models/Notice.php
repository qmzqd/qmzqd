<?php
/**
 * @link http://simpleforum.org/
 * @copyright Copyright (c) 2015 SimpleForum
 * @author Jiandong Yu admin@simpleforum.org
 */

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Notice extends ActiveRecord
{
    const TYPE_COMMENT = 1;
    const TYPE_MENTION = 2;
    const TYPE_FOLLOW_TOPIC = 3;
    const TYPE_FOLLOW_USER = 4;
    const TYPE_GOOD_TOPIC = 5;
    const TYPE_GOOD_COMMENT = 6;
    const TYPE_MSG = 9;
    const TYPE_CHARGE_POINT = 50;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notice}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_id', 'source_id', 'type', 'topic_id', 'position', 'notice_count', 'status'], 'integer'],
            ['msg', 'trim'],
            ['msg', 'string', 'max'=>255],
        ];
    }

    public function getSource()
    {
        return $this->hasOne(User::className(), ['id' => 'source_id'])
                ->select(['id', 'username', 'name', 'avatar']);
    }

    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id'])
                ->select(['id', 'title']);
    }

    private static function findMentions($text)
    {
        if ( !preg_match_all(User::USER_MENTION_PATTERN, $text, $out, PREG_PATTERN_ORDER) ) {
            return false;
        }
        return array_unique($out[1]);
    }

    private static function addMentions($from)
    {
        if ( !($targetNames = self::findMentions($from['text'])) ) {
            return false;
        }
        unset($from['text']);
        if ( !($targets = User::find()->select('id')->where(['in', 'username', $targetNames])->asArray()->all()) ) {
            return false;
        }
        $topicAuthorNoticed = false;
        $topicAuthor = false;
        if ( !empty($from['topic_author'])) {
            $topicAuthor = $from['topic_author'];
            unset($from['topic_author']);
        }

        foreach($targets as $target) {
            if ($target['id'] == $from['source_id']) {
                continue;
            }
            if ( !$topicAuthorNoticed && $topicAuthor && $target['id'] == $topicAuthor ) {
                $topicAuthorNoticed = true;
            }
            $notice = new Notice($from);
            $notice->target_id = $target['id'];
            $notice->save(false);
        }
        return $topicAuthorNoticed;
    }

    private static function addComment($from)
    {
        $notice = Notice::findOne(['type'=>self::TYPE_COMMENT, 'topic_id'=>$from['topic_id'], 'status'=>0]);
        if ($notice) {
            if( $notice->position == $from['position']) {
                return;
            } else {
                $notice->updateCounters(['notice_count' => 1]);
                return;
            }
        }

        $notice = new Notice($from);
        $notice->save(false);
    }

    public static function afterCommentInsert($comment)
    {
        $topicAuthorNoticed = self::addMentions([
            'type' => self::TYPE_MENTION,
            'text' => $comment->content,
            'source_id' => $comment->user_id,
            'topic_id' => $comment->topic_id,
            'position' => $comment->position,
            'topic_author' => $comment->topic->user_id,
        ]);

        if( $comment->user_id != $comment->topic->user_id && !$topicAuthorNoticed ) {
            self::addComment([
                'type' => self::TYPE_COMMENT,
                'source_id' => $comment->user_id,
                'target_id' => $comment->topic->user_id,
                'topic_id' => $comment->topic_id,
                'position' => $comment->position,
            ]);
        }
    }

    public static function afterTopicInsert($topicContent)
    {
        return self::addMentions([
            'type' => self::TYPE_MENTION,
            'text' => $topicContent->content,
            'source_id' => $topicContent->topic->user_id,
            'topic_id' => $topicContent->topic_id,
            'position'=> 0,
        ]);
    }

    public static function afterTopicDelete($topic_id)
    {
        return static::deleteAll(['type'=>[self::TYPE_COMMENT, self::TYPE_MENTION, self::TYPE_FOLLOW_TOPIC], 'target_id'=>$topic_id]);
    }

    public static function afterFollow($favorite)
    {
        $types = [
            Favorite::TYPE_TOPIC => self::TYPE_FOLLOW_TOPIC,
            Favorite::TYPE_USER => self::TYPE_FOLLOW_USER,
        ];
        $notice = new Notice([
            'type' => $types[$favorite->type],
            'source_id' => $favorite->source_id,
        ]);
        if ( $favorite->type == Favorite::TYPE_TOPIC) {
            $notice->topic_id = $favorite->target_id;
            $notice->target_id = $favorite->topic->user_id;
            if ($notice->source_id == $notice->target_id) {
                return true;
            }
        } else {
            $notice->target_id = $favorite->target_id;
        }
        return $notice->save(false);
    }

}

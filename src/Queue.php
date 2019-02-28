<?php
/**
 * Queue.php
 * Author: ithua
 * Date: 2019/2/26 15:37
 * motto:努力到无能为力 拼搏到感动自己
 */
namespace ithuatools\queuemailer;
use ithuatools\queuemailer\Message;
use yii\web\ServerErrorHttpException;

class Queue extends \yii\swiftmailer\Mailer {
    public $messageClass = 'ithuatools\queuemailer\Message';
    public $key = 'mailerKey';
    public $db = '1';

    public function process() {
        $redis = \Yii::$app->redis;
        if(empty($redis)) {
            throw new \yii\base\InvalidConfigException('没有配置redis');
        }
        if($redis->select($this->db) && $messages = $redis->lrange($this->key,0,-1)) {
            $obj = new Message();
           // var_dump($messages);return;
            foreach($messages as $message) {
                $message = json_decode($message,true);
                if(empty($message) || !$this->setMessage($obj,$message)) {
                    throw new ServerErrorHttpException('message error');
                }
                if($obj->send()) {
                    $redis->lrem($this->key,-1,json_encode($message));
                }
            }
        }
    }
    public function setMessage($obj,$message) {
        if(empty($obj)) {
           return false;
        }
        if(!empty($message['from'] && !empty($message['to']))) {
            $obj ->setFrom($message['from']);
            $obj->setTo($message['to']);
            if(!empty($message['cc'])) {
                $obj->setCc($message['cc']);
            }
            if(!empty($message['bcc'])) {
                $obj->setBcc($message['bcc']);
            }
            if(!empty($message['reply_to'])) {
                $obj->setReplyTo($message['reply_to']);
            }
            if(!empty($message['subject'])) {
                $obj->setSubject($message['subject']);
            }
            if(!empty($message['charset'])) {
                $obj->setCharset($message['charset']);
            }
            if(!empty($message['text_body'])) {
                $obj->setTextBody($message['text_body']);
            }
            if(!empty($message['html_body'])) {
                $obj->setTextBody($message['html_body']);
            }
            return $obj;
        }
        return false;

    }
}
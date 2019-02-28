<?php
/**
 * Message.php
 * Author: ithua
 * Date: 2019/2/26 10:02
 * motto:努力到无能为力 拼搏到感动自己
 */
namespace ithuatools\queuemailer;
use Yii;

class Message extends \yii\swiftmailer\Message
{
     public function queue() {
         $redis = Yii::$app->redis;

         if(!$redis) {
             throw new \yii\base\InvalidConfigException('redis没有配置');
         }
         $mailer = Yii::$app->mailer;
         if(empty($mailer) || !$redis->select($mailer->db)) { //选择redis库 0-15
             throw new \yii\base\InvalidConfigException('配置项错误11');
         }
         $message = [];

         $message['from'] = array_keys($this->from);
         $message['to'] = array_keys($this->getTo());

         $message['subject'] = $this->getSubject();
         if(!empty($this->getCc())) {
             $message['cc'] = array_keys($this->getCc());
         }
         if(!empty($this->getBcc())) {
             $message['bcc'] = array_keys($this->getBcc());
         }

         if(!empty($this->getReplyTo())) {
             $message['replyTo'] = array_keys($this->getReplyTo());
         }

//         if(!empty($this->getCharset())) {
//             $message['charset'] = array_keys($this->getCharset());
//         }

         $parts = $this->getSwiftMessage()->getChildren(); //内容（子内容）

        if(!is_array($parts) || !sizeof($parts)) {
             $parts = [$this->getSwiftMessage()];
         }
        // var_dump($parts);return;
         foreach($parts as $part) {
            if(!$part instanceof \Swift_Mime_Attachment) {

                switch($part->getContentType()) {
                    case 'text/html':
                        $message['html_body'] = $part->getBody();
                        break;
                    case 'text/plain':
                        $messge['html_body'] = $part->getBody();
                        break;

                }
//                if(!$message['charset']) {
//                    $message['charset'] = $part->getCharset();
//         }
            }



         }
        // $mailer->mailerKey;
        $key = 'mailerKey';

//       $list = $redis->lrange($key,0,-1);
//       var_dump($list); return;
////       foreach($list as $k=>$v) {
////          $redis->lrem($key,0,$v);
////       }

        return $redis->rpush($key,json_encode($message));
     }
}
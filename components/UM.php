<?php
namespace app\components;


use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\HttpException;

class UM extends Component
{
    public function __construct()
    {
        $this->footer = Yii::t('app', "<p>
                                            <strong>Best regards
                                                        <a href=\"{url}\">stakr.io</a>
                                            </strong>
                                            <p>
                                                Our contacts:<br>
                                                email: m.gibson@flewid.ca<br>
                                            </p><br>
                                       </p>", array(
                                                        'url'=>Yii::$app->getUrlManager()->createAbsoluteUrl('/'),
                                                    ));
        $this->senderEmail = Yii::$app->params['adminEmail'];
        $this->senderFullName = Yii::$app->name;
    }
    //private static $instance;  // экземпляра объекта
    //private function __construct(){ /* ... @return Singleton */ }  // Защищаем от создания через new Singleton
    //private function __clone()    { /* ... @return Singleton */ }  // Защищаем от создания через клонирование
    //private function __wakeup()   { /* ... @return Singleton */ }  // Защищаем от создания через unserialize
    /*
    public static function getInstance() {    // Возвращает единственный экземпляр класса. @return Singleton
        if ( empty(self::$instance) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    */
    public static function getNewInstance() {
        return new self();
    }
    public $footer;
    public $subject;
    public $headTitle;
    public $text;
    public $recipientEmail;
    public $recipientFullName;
    public $senderEmail;
    public $senderFullName;
    public $body;
    public $copyMail=true;
    private function initAttributes($subject=false, $text=false, $recipientEmail=false, $recipientFullName=false, $senderEmail=false, $senderFullName=false, $footer=false)
    {
        if($subject)
            $this->subject=$subject;
        if($text)
            $this->text=$text;
        if($recipientEmail)
            $this->recipientEmail=$recipientEmail;
        if($recipientFullName)
            $this->recipientFullName=$recipientFullName;
        if($senderEmail)
            $this->senderEmail=$senderEmail;
        if($senderFullName)
            $this->senderFullName=$senderFullName;
        if($footer!==false)
            $this->footer=$footer;
            
        if(!$this->headTitle)
            $this->headTitle = $this->subject;
    }
    private function sendByMail()
    {
        if(!$this->recipientEmail)
            throw new HttpException(500, 'E-mail получателя для отправки почты не указан.');
        if(!$this->senderEmail)
            throw new HttpException(500,'E-mail отправителя для отправки почты не указан.');
        if(!$this->senderFullName)
            throw new HttpException(500,'Имя отправителя для отправки почты не указано.');
        
        $headers = "Content-type: text/html; charset=utf-8 \r\n";
        $headers .= "From: {$this->senderFullName} <{$this->senderEmail}>\r\n";

        $to = $this->recipientFullName ? "{$this->recipientFullName} <{$this->recipientEmail}>":$this->recipientEmail;
        if($_SERVER['SERVER_NAME']!='localhost' && $_SERVER['SERVER_NAME']!='yii')
        {
            mail($to, $this->subject, $this->body, $headers);
        }
        else
        {
            $newFileName="../mail/".$this->subject."(".date('Y-m-d H-i-s').").html";
            if(Yii::$app->language=='ru')
                $newFileName = iconv("UTF-8","",$newFileName);
            copy("../mail/example.html", $newFileName);
            $f = fopen($newFileName, "w");
            fwrite($f, $this->body);
            fclose($f);
        }
    }
    public function send($subject=false, $text=false, $recipientEmail=false, $recipientFullName=false, $senderEmail=false, $senderFullName=false, $footer=null)
    {
        $this->initAttributes($subject, $text,$recipientEmail, $recipientFullName, $senderEmail, $senderFullName, $footer);
        $this->body = "<html>
                            <head>
                                <meta charset=\"UTF-8\">
                                <title>{$this->headTitle}</title>
                            </head>
                            <body>
                                {$this->text}
                                {$this->footer} 
                            </body>
                      </html>";
        $this->sendByMail();
    }
}

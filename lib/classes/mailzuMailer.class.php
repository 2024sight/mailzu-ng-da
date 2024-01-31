<?php

/**
 * Use PHPMailer as a base class and extend it
 */
class mailzuMailer extends PHPMailer\PHPMailer\PHPMailer
{
    /**
     * mailzuMailer constructor.
     *
     * @param bool|null $exceptions
     * 
     */
    public function __construct($exceptions)
    {
        global $conf;
        global $charset;

        //Don't forget to do this or other things may not be set correctly!
        parent::__construct($exceptions);
        $this->CharSet = (( isset( $charset )) ? $charset : 'utf-8' );

        if ($lang = determine_language()) {    // Functions exist in the langs.php file
            $this->setLanguage($lang);
        }

        switch (( isset( $conf['app']['emailType'] )) ? $conf['app']['emailType'] : 'mail' ) {
            case 'smtp':
                $this->isSMTP();
                $this->Mailer = 'smtp';
                $this->Host = (( isset( $conf['app']['smtpHost'] )) ? $conf['app']['smtpHost'] : 'localhost' );
                $this->Port = (( isset( $conf['app']['smtpPort'] )) ? $conf['app']['smtpPort'] : 25          );
                break;
            case 'sendmail':
                $this->isSendmail();
                $this->Mailer = 'sendmail';
                $this->Sendmail = (( isset( $conf['app']['sendmailPath'] )) ? $conf['app']['sendmailPath'] : '/usr/sbin/sendmail' );
                break;
            case 'qmail':
                $this->isQmail();
                $this->Mailer = 'qmail';
                $this->Sendmail = (( isset( $conf['app']['qmailPath'] )) ? $conf['app']['qmailPath'] : '/var/qmail/bin/sendmail' );
                break;
            case 'mail':
            default:
                $this->isMail();
                $this->Mailer = 'mail';
        }
    }

    //Create the Send() function
    public function Send()
    {
	global $conf;

        $this->XMailer = ( isset( $conf['app']['mailer'] ) ? $conf['app']['mailer'] : 'mailzu-ng mailer' );

        $r = parent::send();
        return $r;
    }

    //Create the AddAddress() function
    public function AddAddress($address, $name = '')
    {
        return parent::addAddress($address, $name);
    }
}

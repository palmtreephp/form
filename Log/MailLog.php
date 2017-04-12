<?php

namespace Palmtree\Form\Log;

use Palmtree\ArgParser\ArgParser;
use Palmtree\Form\Form;

class MailLog implements LogInterface
{
    /** @var  Form $form */
    protected $form;
    protected $mailer;
    protected $subject = 'New form submission';
    protected $from;
    protected $recipient;
    protected $cc;
    protected $bcc;

    public function __construct(array $args = [])
    {
        $this->parseArgs($args);
        $this->mailer = new \PHPMailer();
    }

    public function log()
    {
        $this->addAddresses($this->recipient, 'addAddress');
        $this->addAddresses($this->cc, 'addCC');
        $this->addAddresses($this->bcc, 'addBCC');

        $this->mailer->setFrom($this->from);
        $this->mailer->subject = $this->subject;

        $this->mailer->Body = $this->getMailBody();

        $result = $this->mailer->send();

        return $result;
    }

    protected function getMailBody()
    {
        $message = '';

        foreach ($this->form->getFields() as $field) {
            $message .= "{$field->getLabel()}: {$field->getData()}\n";
        }

        return $message;
    }

    protected function addAddresses($addresses, $method)
    {
        if (!is_callable([$this->mailer, $method])) {
            return false;
        }

        foreach ((array)$addresses as $name => $address) {
            if (!is_string($name)) {
                $name = '';
            }

            $this->mailer->$method($address, $name);
        }
    }

    protected function parseArgs($args)
    {
        $parser = new ArgParser($args);

        $parser->parseSetters($this);
    }

    /**
     * @param mixed $form
     *
     * @return MailLog
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @param mixed $recipient
     *
     * @return MailLog
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @param mixed $cc
     *
     * @return MailLog
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @param mixed $bcc
     *
     * @return MailLog
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

}

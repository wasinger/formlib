<?php
namespace Wa72\Formlib;
/**
 * FormprocessorSwiftmailer sends the submitted form data by e-mail
 * using Swift_Mailer
 *
 */
class FormprocessorSwiftmailer implements FormprocessorInterface
{
    /**
     * @var FormprocessorMailConfiguration
     */
    protected $configuration;
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $failed_recipients = array();

    /**
     * @param FormprocessorMailConfiguration $configuration
     * @param \Swift_Mailer $mailer
     */
    public function __construct(FormprocessorMailConfiguration $configuration, \Swift_Mailer $mailer)
    {
        $configuration->validate();
        $this->configuration = $configuration;
        $this->mailer = $mailer;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @return FormprocessorSwiftmailer
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param Form $form
     * @return bool
     */
    public function processForm(Form $form)
    {
        $data = $form->getData(true);
        $text = $this->render_results($data);

        $this->failed_recipients = array();
        $mo = $this->configuration;

        $fromfield = $mo->senderfield;
        if ($fromfield && $form->get($fromfield) instanceof Field && $form->get($fromfield)->getData()) {
            $from = $form->get($fromfield)->getData();
        } else {
            $from = $mo->from;
        }

        $message = \Swift_Message::newInstance();
        $message->setSubject($mo->subject)
            ->setTo($mo->to)
            ->setBody($mo->text_pre . $text .$mo->text_post);
        if ($from) $message->setFrom($from);
        if ($from && $from != $mo->from) $message->addReplyTo($from);
        if ($mo->bcc) {
            $message->setBcc($mo->bcc);
        }
        if ($mo->cc) {
            $message->setCc($mo->cc);
        }

        // Logger einschalten
        //$logger = new \Swift_Plugins_Loggers_ArrayLogger();
        //$this->mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));

        $result = $this->mailer->send($message, $this->failed_recipients);
        if ($result > 0 && $result >= count(array_keys($message->getTo()))) {
            $this->logger->info('Form {form} sent to {recipients}',
                array('form' => $form->getName(), 'recipients' => join(', ', array_keys($message->getTo()))));
        } else {
            $this->logger->error('error while sending form {form} to {recipients}',
                array('form' => $form->getName(), 'recipients' => join(', ', $this->failed_recipients))
            );
            //if ($this->transporterrorlogger instanceof Zend_Log) $this->transporterrorlogger->err("Mail transport error:\n\n" . $logger->dump());
            //if ($this->messagelogger instanceof Zend_Log) $this->messagelogger->err("Failed Message:\n\n" . $message->toString());
        }

        if ($mo->copy_to_sender) {
            $tofield = ($mo->senderfield ?: 'email');
            try {
                $to = $form->get($tofield)->getData();
            } catch (\Exception $e) {
                $to = null;
            }
            if ($to) {
                $message = \Swift_Message::newInstance();
                $message->setSubject($mo->subject)
                    ->setTo($to)
                    ->setBody($mo->text_pre_sender . $text . $mo->text_post_sender);
                if ($mo->from) $message->setFrom($mo->from);
                $result = $this->mailer->send($message);
                if ($result > 0) {
                    $this->logger->info('Copy of Form {form} sent to sender {sender}',
                        array('form' => $form->getName(), 'sender' => join(', ', array_keys($message->getTo()))));
                } else {
                    $this->logger->warning('Error while sending copy of form {form} to sender {sender}',
                        array('form' => $form->getName(), 'sender' => join(', ', array_keys($message->getTo()))));
                    //if ($this->transporterrorlogger instanceof Zend_Log) $this->transporterrorlogger->err("Mail transport error:\n\n" . $logger->dump());
                    //if ($this->messagelogger instanceof Zend_Log) $this->messagelogger->err("Failed Message:\n\n" . $message->toString());
                }
            }
        }
        return !((bool)count($this->failed_recipients));
    }

    /**
     * @param array $data Associative Array $label => $value
     * @return string
     */
    protected function render_results($data)
    {
        $s = '';
        foreach ($data as $label => $value) {
            if (is_array($value)) $value = join(', ', $value);
            $s .= "\n" . $label . ":\n";
            $s .= $value . "\n";
        }
        return $s;
    }

}

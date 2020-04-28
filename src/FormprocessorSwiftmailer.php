<?php
namespace Wa72\Formlib;
use Wa72\Formlib\Field\Field;
use Wa72\Formlib\Field\Heading;

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
        $data = $form->getDataWithLabels();
        $text = $this->render_results($data);

        $this->failed_recipients = array();
        $mo = $this->configuration;

        $message = new \Swift_Message;
        $message->setSubject($mo->subject)
            ->setTo($mo->to)
            ->setFrom($mo->from)
            ->setBody($mo->text_pre . $text .$mo->text_post);
        $replyfield = $mo->senderfield;
        if ($replyfield && $form->get($replyfield) instanceof Field && $form->get($replyfield)->getData()) {
            $message->addReplyTo($form->get($replyfield)->getData());
        }
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
        if ($result > 0) {
            $this->logger->info(sprintf('Form %s sent to %s',
                $form->getName(),
                join(', ', array_keys($message->getTo()))
            ));
        } else {
            $this->logger->error(sprintf('error while sending form %s to %s',
                $form->getName(),
                join(', ', $this->failed_recipients)
            ));
        }

        if ($mo->copy_to_sender) {
            $tofield = ($mo->senderfield ?: 'email');
            try {
                $to = $form->get($tofield)->getData();
            } catch (\Exception $e) {
                $to = null;
            }
            if ($to) {
                $message = new \Swift_Message();
                $message->setSubject($mo->subject)
                    ->setTo($to)
                    ->setBody($mo->text_pre_sender . $text . $mo->text_post_sender);
                if ($mo->from) $message->setFrom($mo->from);
                $result = $this->mailer->send($message);
                if ($result > 0) {
                    $this->logger->info(sprintf('Copy of Form %s sent to sender %s',
                        $form->getName(),
                        join(', ', array_keys($message->getTo()))
                    ));
                } else {
                    $this->logger->warning(sprintf('Error while sending copy of form %s to sender %s',
                        $form->getName(),
                        join(', ', array_keys($message->getTo()))
                    ));
                }
            }
        }
        return !((bool)count($this->failed_recipients));
    }

    /**
     * @param array $data Associative Array $name => ['label' => $label, 'value' => $value, 'class' => $class]
     * @return string
     */
    protected function render_results($data)
    {
        $s = '';
        foreach ($data as $name => $fd) {
            $value = $fd['value'];
            $label = $fd['label'];
            $class = $fd['class'];
            if ($class == Heading::class) {
                $s .= "\n\n" . \strtoupper($value) . "\n";
            } else {

                if (is_array($value)) {
                    $value = join(', ', $value);
                }
                $s .= "\n" . $label . ":\n";
                $s .= $value . "\n";
            }
        }
        return $s;
    }

    /**
     * @return FormprocessorMailConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

}

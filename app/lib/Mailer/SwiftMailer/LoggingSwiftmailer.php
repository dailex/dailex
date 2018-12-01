<?php

namespace Dailex\Mailer\SwiftMailer;

use Dailex\Mailer\MailInterface;
use Dailex\Mailer\MailerServiceInterface;
use Dailex\Mailer\MessageConfigurationException;
use Psr\Log\LoggerInterface;
use Swift_Attachment;
use Swift_EmbeddedFile;
use Swift_Mailer;
use Swift_Message;
use Swift_Plugins_LoggerPlugin;
use Swift_Plugins_Loggers_ArrayLogger;
use Swift_Plugins_MessageLogger;
use Swift_Preferences;

/**
 * Handles the sending of mails via a Swift_Mailer instance.
 */
final class LoggingSwiftmailer implements MailerServiceInterface
{
    /**
     * @var ArrayConfig with all mailers and the default mailer settings from mail.xml
     */
    protected $mailer_configs;

    /**
     * @var Swift_Mailer
     */
    protected $swift_mailer = null;

    /**
     * @var Swift_Plugins_Loggers_ArrayLogger
     */
    protected $swift_array_logger = null;

    /**
     * @var Swift_Plugins_MessageLogger
     */
    protected $swift_message_logger = null;

    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * @param Swift_Mailer $mailer instance used to send mails
     * @param ConfigInterface $mailer_configs config with all mailers and the default mailer settings from mail.xml
     * @param LoggerInterface $logger instance to use for communication and message logging
     */
    public function __construct(Swift_Mailer $mailer, array $mailer_configs, LoggerInterface $logger)
    {
        $this->swift_mailer = $mailer;
        $this->mailer_configs = $mailer_configs;
        $this->logger = $logger;

        // to enable logging of communication and sent messages
        $this->swift_array_logger = new Swift_Plugins_Loggers_ArrayLogger;
        $this->swift_message_logger = new Swift_Plugins_MessageLogger;
        $this->swift_mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($this->swift_array_logger));
        $this->swift_mailer->registerPlugin($this->swift_message_logger);
    }

    /**
     * Returns the internally used mailer instance to allow for more advanced
     * use cases where the simple MailInterface is not sufficient at all.
     *
     * @return Swift_Mailer instance used internally for mailing
     */
    public function getMailer()
    {
        return $this->swift_mailer;
    }

    /**
     * Sends a mail via the configured mailer.
     *
     * @param MailInterface $message mail to send
     * @param string $mailer_config_name name of the mailer config to get settings from
     *
     * @return array with the following keys: 'sent_mails' (number of mails sent)
     *              and 'failed_recipients' (email addresses that were not accepted by the internal transport)
     *
     * @throws MessageConfigurationException in case of invalid message configurations
     * @throws \InvalidArgumentException in case of unknown mailer name from config
     */
    public function send(MailInterface $message, $mailer_config_name = null)
    {
        $settings = $this->getMailerSettings($mailer_config_name);

        $mail = $this->createSwiftMessage($message, $mailer_config_name);

        $failed_recipients = [];
        $sent_mails = [];

        if (($settings['send_messages'] ?? true) === true) {
            $sent_mails = $this->swift_mailer->send($mail, $failed_recipients);
        } else {
            $mailer_str = 'the default mailer';
            if ($mailer_config_name !== null) {
                $mailer_str = 'mailer "' . $mailer_config_name . '"';
            }
            $this->logger->debug(
                sprintf(
                    'Mail not sent as "send_messages" setting is disabled for %s.',
                    $mailer_str
                )
            );
        }

        if (false !== ($settings['logging_enabled'] ?? false)) {
            $this->logger->info($this->swift_array_logger->dump());
            if (false !== ($settings['log_messages'] ?? false)) {
                foreach ($this->swift_message_logger->getMessages() as $message) {
                    $this->logger->info($message);
                }
                $this->swift_message_logger->clear();
            }
            $this->swift_array_logger->clear();
        }

        return [
            self::SENT_MAILS => $sent_mails,
            self::FAILED_RECIPIENTS => $failed_recipients
        ];
    }

    /**
     * Create Swift_Message instance from the given MailInterface instance.
     *
     * @param MailInterface $message instance to create Swift_Message from
     * @param string $mailer_config_name name of the mailer config to get settings from
     *
     * @return Swift_Message instance
     *
     * @throws MessageConfigurationException in case of misconfigured message
     * @throws \InvalidArgumentException in case of unknown mailer name from config
     */
    public function createSwiftMessage(MailInterface $message, $mailer_config_name = null)
    {
        $settings = $this->getMailerSettings($mailer_config_name);
        $message_defaults = (array)($settings['address_defaults'] ?? []);
        $message_overrides = (array)($settings['address_overrides'] ?? []);

        Swift_Preferences::getInstance()->setCharset($settings['charset'] ?? 'utf-8');

        $mail = Swift_Message::newInstance();

        $mail->setSubject($message->getSubject($settings['default_subject'] ?? null));

        $from = $message_overrides['from'] ?? $message->getFrom($message_defaults['from'] ?? null);
        if (!empty($from)) {
            $mail->setFrom($from);
        }

        $sender = $message_overrides['sender'] ?? $message->getSender($message_defaults['sender'] ?? null);
        if (!empty($sender)) {
            $mail->setSender($sender);
        }

        // sender is mandatory if multiple from addresses are set
        if (is_array($from) && count($from) > 1 && empty($sender)) {
            throw new MessageConfigurationException(
                'A single "sender" email address must be specified when multiple "from" email addresses are set.'
            );
        }

        // we need at least a sender or a from to be honest citizens
        if (empty($from) && empty($sender)) {
            throw new MessageConfigurationException(
                'Either "from" or "sender" must be set with a valid email address on a message.' .
                'Usually "from" is considered to be mandatory with the "sender" being optional ' .
                'to distinguish between writers of an email and its actual sender.'
            );
        }

        $reply_to = $message_overrides['reply_to'] ?? $message->getReplyTo($message_defaults['reply_to'] ?? null);

        if (!empty($reply_to)) {
            $mail->setReplyTo($reply_to);
        }

        $return_path = $message_overrides['return_path']
            ?? $message->getReturnPath($message_defaults['return_path'] ?? null);

        if (!empty($return_path)) {
            // Swift only wants a string as email on the return path (despite the behaviour on other address fields)
            if (is_array($return_path)) {
                $return_path = array_keys($return_path);
                $return_path = array_shift($return_path);
            }
            $mail->setReturnPath($return_path);
        }

        $date = $message->getDate($message_defaults['date'] ?? null);
        if (isset($settings['default_date'])) {
            $date = strtotime($settings['default_date']);
        }

        if (!empty($date) && is_int($date)) {
            $mail->setDate($date);
        }

        $to = $message_overrides['to'] ?? $message->getTo($message_defaults['to'] ?? null);
        if (!empty($to)) {
            $mail->setTo($to);
        }

        $cc = $message_overrides['cc'] ?? $message->getCc($message_defaults['cc'] ?? null);
        if (!empty($cc)) {
            $mail->setCc($cc);
        }

        $bcc = $message_overrides['bcc'] ?? $message->getBcc($message_defaults['bcc'] ?? null);
        if (!empty($bcc)) {
            $mail->setBcc($bcc);
        }

        $body_html = $message->getBodyHtml($message_defaults['default_body_html'] ?? null);
        if (!empty($body_html)) {
            $mail->addPart($body_html, "text/html");
        }

        $body_text = $message->getBodyText($message_defaults['default_body_text'] ?? null);
        if (!empty($body_text)) {
            $mail->addPart($body_text, "text/plain");
        }

        $attachments = $message->getAttachments();
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (!is_array($attachment)) {
                    continue;
                }

                $mail->attach($this->createSwiftAttachment($attachment));
            }
        }

        // if to, cc or bcc are set we have to override them if a setting is set
        $override_all_recipients = $settings['override_all_recipients'] ?? false;
        if (false !== $override_all_recipients && !empty($override_all_recipients)) {
            if (!empty($to)) {
                $to = $override_all_recipients; // for later validation
                $mail->setTo($to);
            }

            if (!empty($cc)) {
                $cc = $override_all_recipients; // for later validation
                $mail->setCc($cc);
            }

            if (!empty($bcc)) {
                $bcc = $override_all_recipients; // for later validation
                $mail->setBcc($bcc);
            }
        }

        // we won't send mails without recipients
        if (empty($to) && empty($cc) && empty($bcc)) {
            throw new MessageConfigurationException(
                'No recipients are set for this email. Set "to", "cc" and/or "bcc" email addresses on the message.'
            );
        }

        // do not allow too long text lines
        if (isset($settings['max_line_length'])) {
            $mail->setMaxLineLength((int)($settings['max_line_length'] ?? 78));
        }

        // add X-Priority header
        if (isset($settings['priority'])) {
            $mail->setPriority((int)($settings['priority'] ?? 3));
        }

        // request read receipts if necessary
        if (isset($settings['read_receipt_to'])) {
            $mail->setReadReceiptTo($settings['read_receipt_to']);
        }

        return $mail;
    }

    /**
     * Returns the settings of the given mailer.
     *
     * @param string $mailer_name name of mailer to get settings for
     *
     * @return Settings for the given mailer name (or the default mailer)
     */
    protected function getMailerSettings($mailer_name = null)
    {
        if (null === $mailer_name) {
            return $this->mailer_configs[self::DEFAULT_MAILER_NAME] ?? [];
        }

        $all_mailers = $this->mailer_configs[self::DEFAULT_MAILERS_KEY] ?? [];
        if (!isset($all_mailers[$mailer_name])) {
            throw new \InvalidArgumentException('There are no mailer settings for name: '.$mailer_name);
        }

        return $all_mailers[$mailer_name];
    }

    /**
     * Attachment array keys:
     * - 'path'
     * - 'content_disposition'
     * - 'content_type'
     * - 'name'
     *
     * @param array $attachment information about the attachment to create
     *
     * @return Swift_Attachment or Swift_EmbeddedFile
     */
    protected function createSwiftAttachment(array $attachment)
    {
        $file = '';
        if (MailInterface::CONTENT_DISPOSITION_INLINE === $attachment['content_disposition']) {
            if (!empty($attachment['path'])) {
                $file = Swift_EmbeddedFile::fromPath($attachment['path']);
                $file->setFilename($attachment['name']);
                $file->setContentType($attachment['content_type']);
            } else {
                $file = Swift_EmbeddedFile::newInstance(
                    $attachment['content'],
                    $attachment['name'],
                    $attachment['content_type']
                );
            }
        } elseif (MailInterface::CONTENT_DISPOSITION_ATTACHMENT === $attachment['content_disposition']) {
            if (!empty($attachment['path'])) {
                $file = Swift_Attachment::fromPath($attachment['path']);
                $file->setFilename($attachment['name']);
                $file->setContentType($attachment['content_type']);
            } else {
                $file = Swift_Attachment::newInstance(
                    $attachment['content'],
                    $attachment['name'],
                    $attachment['content_type']
                );
            }
        } else {
            throw new MessageConfigurationException(
                'Could not use given attachment '.print_r($attachment, true).
                ' in '. __METHOD__.'. Use correct array or path instead.'
            );
        }

        return $file;
    }
}

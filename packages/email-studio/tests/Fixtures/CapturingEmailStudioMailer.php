<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Tests\Fixtures;

use BadMethodCallException;
use Closure;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Message;
use Illuminate\Mail\PendingMail;
use Illuminate\Mail\SentMessage as IlluminateSentMessage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage as SymfonySentMessage;
use Symfony\Component\Mime\Email as SymfonyEmail;

final class CapturingEmailStudioMailer implements MailerContract
{
    public ?SymfonyEmail $message = null;

    public string $transportMessageId = 'smtp-provider-message-id';

    public function to(mixed $users): PendingMail
    {
        throw new BadMethodCallException('The coverage mailer only supports send().');
    }

    public function bcc(mixed $users): PendingMail
    {
        throw new BadMethodCallException('The coverage mailer only supports send().');
    }

    public function raw(mixed $text, mixed $callback): ?IlluminateSentMessage
    {
        throw new BadMethodCallException('The coverage mailer only supports send().');
    }

    /**
     * @param  array<array-key, mixed>  $view
     * @param  array<array-key, mixed>  $data
     */
    public function send(mixed $view, array $data = [], mixed $callback = null): ?IlluminateSentMessage
    {
        unset($view, $data);

        $message = new SymfonyEmail;

        if ($callback instanceof Closure) {
            $callback(new Message($message));
        }

        $this->message = $message;

        $sentMessage = new SymfonySentMessage($message, Envelope::create($message));
        $sentMessage->setMessageId($this->transportMessageId);

        return new IlluminateSentMessage($sentMessage);
    }

    /**
     * @param  array<array-key, mixed>  $mailable
     * @param  array<array-key, mixed>  $data
     */
    public function sendNow(mixed $mailable, array $data = [], mixed $callback = null): ?IlluminateSentMessage
    {
        return $this->send($mailable, $data, $callback);
    }
}

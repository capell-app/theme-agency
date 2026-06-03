<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Tests\Fixtures;

use BadMethodCallException;
use Closure;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Message;
use Illuminate\Mail\PendingMail;
use Illuminate\Mail\SentMessage;
use Symfony\Component\Mime\Email as SymfonyEmail;

final class CapturingEmailStudioMailer implements MailerContract
{
    public ?SymfonyEmail $message = null;

    public function to(mixed $users): PendingMail
    {
        throw new BadMethodCallException('The coverage mailer only supports send().');
    }

    public function bcc(mixed $users): PendingMail
    {
        throw new BadMethodCallException('The coverage mailer only supports send().');
    }

    public function raw(mixed $text, mixed $callback): ?SentMessage
    {
        throw new BadMethodCallException('The coverage mailer only supports send().');
    }

    /**
     * @param  array<array-key, mixed>  $view
     * @param  array<array-key, mixed>  $data
     */
    public function send(mixed $view, array $data = [], mixed $callback = null): ?SentMessage
    {
        unset($view, $data);

        $message = new SymfonyEmail;

        if ($callback instanceof Closure) {
            $callback(new Message($message));
        }

        $this->message = $message;

        return null;
    }

    /**
     * @param  array<array-key, mixed>  $mailable
     * @param  array<array-key, mixed>  $data
     */
    public function sendNow(mixed $mailable, array $data = [], mixed $callback = null): ?SentMessage
    {
        return $this->send($mailable, $data, $callback);
    }
}

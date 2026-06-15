<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailContextData;
use Illuminate\Notifications\Messages\MailMessage;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static MailMessage run(string $templateKey, array<string, mixed> $variables)
 */
class BuildAuthEmailMailMessageAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $variables
     */
    public function handle(string $templateKey, array $variables): MailMessage
    {
        RegisterAuthEmailTemplatesAction::run();

        $rendered = RenderResolvedEmailTemplateAction::run(
            templateKey: $templateKey,
            context: new EmailContextData(variables: $variables),
        )->rendered;

        return (new MailMessage)
            ->subject($rendered->subject)
            ->view('capell-email-studio::emails.auth-rendered', [
                'html' => $rendered->html,
                'text' => $rendered->text,
            ]);
    }
}

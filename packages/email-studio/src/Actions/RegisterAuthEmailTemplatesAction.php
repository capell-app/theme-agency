<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Data\EmailTemplateDefinitionData;
use Capell\EmailStudio\Data\EmailTemplateVariableData;
use Capell\EmailStudio\Support\EmailTemplateRegistry;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterAuthEmailTemplatesAction
{
    use AsAction;

    public function handle(): void
    {
        $registry = resolve(EmailTemplateRegistry::class);

        foreach ($this->definitions() as $definition) {
            $registry->registerDefinition($definition);
        }
    }

    /**
     * @return list<EmailTemplateDefinitionData>
     */
    private function definitions(): array
    {
        return [
            $this->definition(
                key: 'auth.verify-email',
                name: 'Verify email address',
                description: 'Replaces Laravel email verification notifications.',
                subject: 'Verify your email address for {{ config.app.name }}',
                previewText: 'Confirm this email address to finish setting up your account.',
                htmlView: 'capell-email-studio::emails.auth.verify-email',
                text: "Verify your email address: {{ action_url }}\n\nThis link expires in {{ expires_minutes }} minutes.",
                variables: ['name', 'email', 'action_url', 'expires_minutes', 'config.app.name'],
                sampleData: [
                    'name' => 'Ben',
                    'email' => 'ben@example.com',
                    'action_url' => 'https://example.com/email/verify/signed-url',
                    'expires_minutes' => 60,
                ],
            ),
            $this->definition(
                key: 'auth.reset-password',
                name: 'Reset password',
                description: 'Replaces Laravel password reset notifications.',
                subject: 'Reset your {{ config.app.name }} password',
                previewText: 'Use this secure link to choose a new password.',
                htmlView: 'capell-email-studio::emails.auth.reset-password',
                text: "Reset your password: {{ action_url }}\n\nThis link expires in {{ expires_minutes }} minutes.",
                variables: ['name', 'email', 'action_url', 'expires_minutes', 'config.app.name'],
                sampleData: [
                    'name' => 'Ben',
                    'email' => 'ben@example.com',
                    'action_url' => 'https://example.com/reset-password/example-token',
                    'expires_minutes' => 60,
                ],
            ),
            $this->definition(
                key: 'auth.welcome',
                name: 'Welcome',
                description: 'Optional welcome email sent after registration.',
                subject: 'Welcome to {{ config.app.name }}',
                previewText: 'Your account is ready.',
                htmlView: 'capell-email-studio::emails.auth.welcome',
                text: "Welcome to {{ config.app.name }}.\n\nSign in: {{ action_url }}",
                variables: ['name', 'email', 'action_url', 'config.app.name'],
                sampleData: [
                    'name' => 'Ben',
                    'email' => 'ben@example.com',
                    'action_url' => 'https://example.com/login',
                ],
            ),
            $this->definition(
                key: 'auth.verified',
                name: 'Email verified',
                description: 'Optional confirmation sent after a user verifies their email address.',
                subject: 'Your {{ config.app.name }} email is verified',
                previewText: 'Your account email has been verified.',
                htmlView: 'capell-email-studio::emails.auth.verified',
                text: 'Your email address for {{ config.app.name }} has been verified.',
                variables: ['name', 'email', 'config.app.name'],
                sampleData: ['name' => 'Ben', 'email' => 'ben@example.com'],
            ),
            $this->definition(
                key: 'auth.login',
                name: 'Login notice',
                description: 'Optional account login notice.',
                subject: 'New login to {{ config.app.name }}',
                previewText: 'A sign-in was recorded for your account.',
                htmlView: 'capell-email-studio::emails.auth.login',
                text: "A login was recorded for {{ email }} at {{ login_time }}.\n\nIP address: {{ ip_address }}",
                variables: ['name', 'email', 'login_time', 'ip_address', 'config.app.name'],
                sampleData: [
                    'name' => 'Ben',
                    'email' => 'ben@example.com',
                    'login_time' => '2026-06-13 10:30',
                    'ip_address' => '127.0.0.1',
                ],
            ),
            $this->definition(
                key: 'auth.lockout',
                name: 'Login lockout',
                description: 'Optional notice sent when login throttling locks an email address.',
                subject: 'Login temporarily locked for {{ config.app.name }}',
                previewText: 'Too many sign-in attempts were made.',
                htmlView: 'capell-email-studio::emails.auth.lockout',
                text: "Too many sign-in attempts were made for {{ email }}.\n\nIP address: {{ ip_address }}",
                variables: ['email', 'ip_address', 'config.app.name'],
                sampleData: ['email' => 'ben@example.com', 'ip_address' => '127.0.0.1'],
            ),
            $this->definition(
                key: 'auth.password-reset-success',
                name: 'Password reset success',
                description: 'Optional confirmation sent after a password reset succeeds.',
                subject: 'Your {{ config.app.name }} password was reset',
                previewText: 'Your password has been changed.',
                htmlView: 'capell-email-studio::emails.auth.password-reset-success',
                text: 'Your password for {{ config.app.name }} was reset.',
                variables: ['name', 'email', 'config.app.name'],
                sampleData: ['name' => 'Ben', 'email' => 'ben@example.com'],
            ),
        ];
    }

    /**
     * @param  list<string>  $variables
     * @param  array<string, mixed>  $sampleData
     */
    private function definition(
        string $key,
        string $name,
        string $description,
        string $subject,
        ?string $previewText,
        string $htmlView,
        ?string $text,
        array $variables,
        array $sampleData,
    ): EmailTemplateDefinitionData {
        return new EmailTemplateDefinitionData(
            key: $key,
            packageName: 'capell-app/email-studio',
            name: $name,
            description: $description,
            variables: array_map(
                static fn (string $variable): EmailTemplateVariableData => new EmailTemplateVariableData(
                    name: $variable,
                    sampleValue: $sampleData[$variable] ?? null,
                ),
                $variables,
            ),
            subject: $subject,
            previewText: $previewText,
            text: $text,
            htmlView: $htmlView,
            defaultThemeKey: 'default',
            sampleData: $sampleData,
        );
    }
}

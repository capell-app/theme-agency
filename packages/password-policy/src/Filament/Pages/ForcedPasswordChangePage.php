<?php

declare(strict_types=1);

namespace Capell\PasswordPolicy\Filament\Pages;

use BackedEnum;
use Capell\PasswordPolicy\Actions\EvaluatePasswordPolicyAction;
use Capell\PasswordPolicy\Actions\UpdatePasswordAction;
use Capell\PasswordPolicy\Data\PasswordChangeData;
use Capell\PasswordPolicy\Data\PasswordPolicyStatusData;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Livewire\Features\SupportRedirects\Redirector;
use Override;

/**
 * @property Schema $form
 */
class ForcedPasswordChangePage extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $slug = 'password-policy/change-password';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'capell-password-policy::filament.pages.forced-password-change';

    #[Override]
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user instanceof Model) {
            return false;
        }

        $status = resolve(EvaluatePasswordPolicyAction::class)->handle($user);
        abort_unless($status instanceof PasswordPolicyStatusData, 403);
        $mustChangePassword = $status->mustChangePassword;
        $passwordExpired = $status->passwordExpired;

        return $mustChangePassword || $passwordExpired;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    #[Override]
    public function getTitle(): string|Htmlable
    {
        return __('capell-password-policy::password_change.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('current_password')
                    ->label(__('capell-password-policy::password_change.current_password'))
                    ->password()
                    ->required()
                    ->autocomplete('current-password'),
                TextInput::make('password')
                    ->label(__('capell-password-policy::password_change.password'))
                    ->password()
                    ->required()
                    ->autocomplete('new-password'),
                TextInput::make('password_confirmation')
                    ->label(__('capell-password-policy::password_change.password_confirmation'))
                    ->password()
                    ->required()
                    ->autocomplete('new-password')
                    ->same('password'),
            ])
            ->statePath('data');
    }

    public function updatePassword(): RedirectResponse|Redirector
    {
        $user = auth()->user();

        abort_unless($user instanceof Model, 403);

        /** @var array{current_password?: string, password?: string, password_confirmation?: string} $data */
        $data = $this->form->getState();

        UpdatePasswordAction::run(
            $user,
            new PasswordChangeData(
                password: $data['password'] ?? '',
                passwordConfirmation: $data['password_confirmation'] ?? '',
                currentPassword: $data['current_password'] ?? '',
            ),
        );

        Notification::make()
            ->success()
            ->title(__('capell-password-policy::password_change.updated'))
            ->send();

        $panelUrl = Filament::getCurrentPanel()?->getUrl() ?? url('/admin');

        return Redirect::to($panelUrl);
    }
}

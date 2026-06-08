<?php

declare(strict_types=1);

namespace Capell\AccessGate\Filament\Widgets;

use Capell\AccessGate\Enums\RegistrationStatus;
use Capell\AccessGate\Filament\Resources\Registrations\RegistrationResource;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Support\AccessGateSiteScope;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Override;

final class PendingAccessRequestsWidget extends BaseWidget
{
    protected static ?string $heading = 'Pending access requests';

    protected static ?int $sort = 3;

    /** @var int|string|array<string, int|string|null> */
    protected int|string|array $columnSpan = 'full';

    #[Override]
    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Registration::class) ?? false;
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->emptyStateHeading(__('No pending access requests'))
            ->columns([
                TextColumn::make('email')
                    ->label(__('capell-access-gate::filament.fields.email'))
                    ->searchable(),
                TextColumn::make('area.key')
                    ->label(__('capell-access-gate::filament.fields.area'))
                    ->badge(),
                TextColumn::make('requested_host')
                    ->label(__('capell-access-gate::filament.fields.requested_host'))
                    ->toggleable(),
                TextColumn::make('requested_at')
                    ->label(__('capell-access-gate::filament.fields.requested_at'))
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('requested_at', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->recordActions([
                Action::make('review')
                    ->label(__('Review'))
                    ->url(fn (Registration $record): string => RegistrationResource::getUrl('index', ['tableSearch' => $record->email])),
            ]);
    }

    /**
     * @return Builder<Registration>
     */
    private function getQuery(): Builder
    {
        return AccessGateSiteScope::applyAreaScope(Registration::query())
            ->with('area')
            ->where('status', RegistrationStatus::Pending)
            ->latest('requested_at');
    }
}

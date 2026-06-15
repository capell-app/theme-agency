<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Actions;

use Capell\EmailStudio\Enums\EmailTemplateStatus;
use Capell\EmailStudio\Enums\EmailVariantStatus;
use Capell\EmailStudio\Models\EmailTemplateVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EmailTemplateVariant run(EmailTemplateVariant $variant, ?int $approvedBy = null)
 */
class ActivateEmailTemplateVariantAction
{
    use AsAction;

    public function handle(EmailTemplateVariant $variant, ?int $approvedBy = null): EmailTemplateVariant
    {
        return DB::transaction(function () use ($variant, $approvedBy): EmailTemplateVariant {
            $variant->loadMissing('template');

            EmailTemplateVariant::query()
                ->where('email_template_id', $variant->email_template_id)
                ->where('site_scope_key', $variant->site_scope_key)
                ->when(
                    $variant->locale === null,
                    static fn (Builder $query): Builder => $query->whereNull('locale'),
                    static fn (Builder $query): Builder => $query->where('locale', $variant->locale),
                )
                ->where('status', EmailVariantStatus::Active)
                ->whereKeyNot($variant->getKey())
                ->update(['status' => EmailVariantStatus::Retired]);

            $variant->forceFill([
                'status' => EmailVariantStatus::Active,
                'approved_at' => now()->toImmutable(),
                'approved_by' => $approvedBy,
            ])->save();

            $variant->template?->forceFill([
                'status' => EmailTemplateStatus::Approved,
            ])->save();

            return $variant->refresh();
        });
    }
}

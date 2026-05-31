<?php

declare(strict_types=1);

use Capell\Blog\Enums\BlogPageTypeEnum;
use Capell\Blog\Models\Article;
use Capell\Blog\Support\Creator\BlogCreator;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\LayoutBuilder\Actions\AddHeroBlockToLayoutAction;
use Capell\LayoutBuilder\Actions\CreateHeroBlockAction;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Support\Creator\DemoCreator;
use Illuminate\Console\Command;
use Mockery\MockInterface;

it('adds hero meta to blog and article pages when blog package is installed', function (): void {
    AddHeroBlockToLayoutAction::shouldRun()->once();
    Blueprint::factory()->type('section')->create(['key' => 'hero']);

    $heroBlock = Widget::factory()->make();
    CreateHeroBlockAction::shouldRun()->twice()->andReturn($heroBlock);

    $demoCreator = mock(DemoCreator::class, function (DemoCreator&MockInterface $mock): void {
        $mock->shouldReceive('createContentsBlock')->once();
    });

    app()->instance(DemoCreator::class, $demoCreator);

    $languages = Language::factory(2)->create();
    $site = Site::factory()
        ->language($languages[0])
        ->state(['name' => 'DemoSite'])
        ->withTranslations($languages)
        ->create();

    Page::factory()->site($site)->home()->withTranslations()->create();

    $blogCreator = resolve(BlogCreator::class);
    $blogCreator->setup($site);

    $articlePage = Article::factory()->site($site)->withTranslations($languages)->create();

    $blogPage = Page::query()->whereRelation('type', 'key', BlogPageTypeEnum::Blog->value)->firstOrFail();

    foreach ($blogPage->translations as $blogTranslation) {
        $meta = $blogTranslation->meta;

        capell_expect($meta)->not()->toHaveKey('hero');
    }

    foreach ($articlePage->translations as $articleTranslation) {
        $meta = $articleTranslation->meta;

        capell_expect($meta)->not()->toHaveKey('hero');
    }

    $this->artisan('capell:hero-demo --sites=DemoSite')
        ->expectsOutput('Demo hero content has been successfully created for site: DemoSite')
        ->expectsOutput('Hero demo content inserted successfully.')
        ->assertExitCode(Command::SUCCESS);

    $expectedBlogHero = '<p>' . __('capell-blog::generic.blog_intro') . '</p>';
    $freshBlogPage = capell_test_instance($blogPage->fresh(), Page::class);

    foreach ($freshBlogPage->translations as $blogTranslation) {
        capell_expect($blogTranslation->meta)
            ->hero->toBe($expectedBlogHero)
            ->hero_title->toBe(__('capell-blog::generic.blog'));
    }

    $freshArticlePage = capell_test_instance($articlePage->fresh(), Article::class);

    foreach ($freshArticlePage->translations as $articleTranslation) {
        capell_expect($articleTranslation->meta)->hero->toBe('<h1>' . $articleTranslation->title . '</h1>');
    }
});

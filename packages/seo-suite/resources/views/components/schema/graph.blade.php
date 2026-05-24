<?php
use Capell\Core\Models\Page;
use Capell\Frontend\Facades\Frontend;
use Capell\SeoSuite\Actions\SchemaGraphAction;

$page = Frontend::page();
$site = Frontend::site();
$language = Frontend::language();

$graphData = $page instanceof Page ? SchemaGraphAction::run($page, $site, $language) : null;

?>

{!! $graphData?->toJsonLdScript() ?? '' !!}

<?php

declare(strict_types=1);

return [
    'admin' => [
        'actions' => [
            'create_article' => 'Create article',
            'create_collection' => 'Create collection',
        ],
        'fields' => [
            'ai_readable' => 'AI readable',
            'articles' => 'Articles',
            'body' => 'Body',
            'collection' => 'Collection',
            'description' => 'Description',
            'key' => 'Key',
            'parent_collection' => 'Parent collection',
            'public' => 'Public',
            'published_at' => 'Published at',
            'search_weight' => 'Search weight',
            'slug' => 'Slug',
            'sort_order' => 'Sort order',
            'status' => 'Status',
            'summary' => 'Summary',
            'title' => 'Title',
            'version' => 'Version',
            'visibility' => 'Visibility',
        ],
        'models' => [
            'article' => 'knowledge base article',
            'articles' => 'knowledge base articles',
            'collection' => 'knowledge base collection',
            'collections' => 'knowledge base collections',
        ],
        'navigation_group' => 'Knowledge base',
        'resources' => [
            'articles' => 'Articles',
            'collections' => 'Collections',
        ],
        'sections' => [
            'article' => 'Article',
            'collection' => 'Collection',
        ],
        'visibility' => [
            'no' => 'No',
            'private' => 'Private',
            'public' => 'Public',
            'yes' => 'Yes',
        ],
    ],
    'article_status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],
    'frontend' => [
        'breadcrumbs' => 'Breadcrumbs',
        'feedback_comment' => 'Optional feedback',
        'feedback_helpful' => 'Helpful',
        'feedback_not_helpful' => 'Not helpful',
        'feedback_submitted' => 'Thanks for your feedback.',
        'feedback_title' => 'Was this article helpful?',
        'index_title' => 'Knowledge base',
        'no_articles' => 'No knowledge base articles are available yet.',
        'related_articles' => 'Related articles',
    ],
    'related_article_type' => [
        'related' => 'Related',
        'prerequisite' => 'Prerequisite',
        'next_step' => 'Next step',
    ],
    'validation' => [
        'article_body_required' => 'Article content is required.',
        'article_title_required' => 'Article title is required.',
        'collection_title_required' => 'Collection title is required.',
        'slug_required' => 'A slug is required.',
    ],
];

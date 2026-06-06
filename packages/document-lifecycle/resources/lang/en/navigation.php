<?php

declare(strict_types=1);

return [
    'group' => 'Document Lifecycle',
    'document' => 'document',
    'documents' => 'Controlled documents',
    'fields' => [
        'key' => 'Key',
        'title' => 'Title',
        'status' => 'Status',
        'metadata' => 'Metadata',
        'content' => 'Content',
        'metadata_note' => 'Admin note',
        'review_due_at' => 'Review due',
        'expires_at' => 'Expires',
        'publications' => 'Publications',
        'version' => 'Version',
        'hash' => 'Hash',
        'revision' => 'Revision',
        'published_at' => 'Published',
        'context' => 'Context',
        'acceptor' => 'Acceptor',
        'accepted_at' => 'Accepted',
        'updated_at' => 'Updated',
        'all_versions' => 'All versions',
    ],
    'relations' => [
        'publications' => 'Publications',
        'acceptances' => 'Acceptances',
    ],
    'actions' => [
        'register_document' => 'Register document',
        'publish_version' => 'Publish version',
        'record_acceptance' => 'Record acceptance',
        'archive_document' => 'Archive document',
        'restore_document' => 'Restore document',
        'export_acceptance_evidence' => 'Export evidence CSV',
        'export_outstanding_acceptances' => 'Export outstanding CSV',
        'download_acceptance_certificate' => 'Download signed JSON',
    ],
    'messages' => [
        'version_published' => 'Document version published.',
        'acceptance_recorded' => 'Document acceptance recorded.',
        'document_archived' => 'Document archived.',
        'document_restored' => 'Document restored.',
    ],
    'commands' => [
        'archive_expired' => [
            'summary' => 'Archived :count expired controlled document(s).',
        ],
    ],
    'portal' => [
        'accepted_status' => 'Accepted',
        'acceptance_description' => 'Accepted version :version.',
    ],
    'status' => [
        'draft' => 'Draft',
        'active' => 'Active',
        'archived' => 'Archived',
    ],
];

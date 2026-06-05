<?php

declare(strict_types=1);

return [
    'empty' => [
        'description' => 'Tracked messages will appear here after Laravel sends mail through MailTracker.',
        'heading' => 'No tracked emails',
    ],
    'fields' => [
        'clicked_at' => 'First clicked',
        'clicks' => 'Clicks',
        'content_stored' => 'Content stored',
        'created_at' => 'Sent at',
        'headers' => 'Headers',
        'message_id' => 'Message ID',
        'opened_at' => 'First opened',
        'opens' => 'Opens',
        'recipient' => 'Recipient',
        'sender' => 'Sender',
        'subject' => 'Subject',
        'tracked_urls' => 'Tracked URLs',
        'updated_at' => 'Updated',
        'url' => 'URL',
    ],
    'filters' => [
        'clicked' => 'Clicked',
        'content_stored' => 'Content stored',
        'created_at' => 'Sent date',
        'from' => 'From',
        'opened' => 'Opened',
        'until' => 'Until',
    ],
    'navigation' => [
        'sent_emails' => 'Sent emails',
    ],
    'no_click_rows' => 'No tracked URL clicks have been recorded for this email.',
    'preview_title' => 'Stored email HTML preview',
    'placeholders' => [
        'empty' => '-',
    ],
    'resources' => [
        'sent_email' => 'sent email',
        'sent_emails' => 'sent emails',
    ],
    'sections' => [
        'clicks' => 'Tracked URL clicks',
        'headers' => 'Headers',
        'overview' => 'Message overview',
        'preview' => 'Stored HTML preview',
    ],
    'subheading' => [
        'sent_emails' => 'View MailTracker sent-email records, opens, clicks, headers, and stored HTML snapshots.',
    ],
];

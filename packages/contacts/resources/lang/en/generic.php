<?php

declare(strict_types=1);

return [
    'fields' => [
        'captured_at' => 'Captured at',
        'display_name' => 'Display name',
        'domain' => 'Domain',
        'email' => 'Email',
        'last_seen_at' => 'Last seen at',
        'name' => 'Name',
        'occurred_at' => 'Occurred at',
        'phone' => 'Phone',
        'status' => 'Status',
        'summary' => 'Summary',
        'title' => 'Title',
        'type' => 'Type',
        'updated_at' => 'Updated at',
        'value_amount' => 'Value',
        'website' => 'Website',
    ],
    'actions' => [
        'privacy_anonymize' => 'Anonymize contact',
        'privacy_export' => 'Export privacy data',
    ],
    'resources' => [
        'activities' => 'Activities',
        'contact' => 'Contact',
        'contacts' => 'Contacts',
        'lead' => 'Lead',
        'leads' => 'Leads',
        'organisation' => 'Organisation',
        'organisations' => 'Organisations',
    ],
    'form_builder' => [
        'activity_summary' => 'Submitted :form form',
        'lead_title' => ':form form submission',
        'unknown_form' => 'Unknown',
    ],
    'access_gate' => [
        'activity_summary' => 'Approved access request for :area',
        'lead_title' => ':area access request',
        'unknown_area' => 'Unknown access area',
    ],
    'events' => [
        'activity_summary' => 'Registered for :event',
        'unknown_event' => 'Unknown event',
    ],
    'campaign_studio' => [
        'activity_summary' => 'Converted on :goal',
        'unknown_goal' => 'Unknown campaign goal',
    ],
    'comments' => [
        'activity_summary' => 'Submitted a comment',
    ],
    'shopify_commerce' => [
        'activity_summary' => 'Synced Shopify customer',
    ],
    'contact_status' => [
        'active' => 'Active',
        'archived' => 'Archived',
        'blocked' => 'Blocked',
    ],
    'organisation_status' => [
        'active' => 'Active',
        'archived' => 'Archived',
    ],
    'lead_status' => [
        'new' => 'New',
        'open' => 'Open',
        'qualified' => 'Qualified',
        'won' => 'Won',
        'lost' => 'Lost',
        'archived' => 'Archived',
    ],
    'activity_type' => [
        'note' => 'Note',
        'form_submission' => 'Form submission',
        'newsletter_subscription' => 'Newsletter subscription',
        'comment' => 'Comment',
        'access_registration' => 'Access registration',
        'event_registration' => 'Event registration',
        'shopify_customer' => 'Shopify customer',
        'campaign_conversion' => 'Campaign conversion',
        'privacy_export' => 'Privacy export',
        'privacy_anonymization' => 'Privacy anonymization',
    ],
    'privacy' => [
        'anonymization_activity_summary' => 'Contact privacy anonymization completed',
        'anonymized_contact' => 'Anonymized contact :contact.',
        'anonymized_notification' => 'Contact anonymized.',
        'command_contact_not_found' => 'No matching contact was found.',
        'command_positive_integer' => 'The :option option must be a positive integer.',
        'command_requires_contact' => 'Provide a contact ID or --email with --site-id.',
        'command_requires_operation' => 'Choose --export, --anonymize, or both.',
        'command_requires_site' => 'The --site-id option is required when resolving by email.',
        'command_single_lookup' => 'Use either a contact ID or --email, not both.',
        'command_valid_contact' => 'The contact argument must be a positive integer ID.',
        'command_valid_email' => 'The --email option must be a valid email address.',
        'export_activity_summary' => 'Contact privacy export generated',
    ],
    'widgets' => [
        'activities' => 'Activities',
        'contacts' => 'Contacts',
        'open_leads' => 'Open leads',
        'organisations' => 'Organisations',
    ],
];

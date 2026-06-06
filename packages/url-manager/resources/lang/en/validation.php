<?php

declare(strict_types=1);

return [
    'import_file_too_large' => 'The redirect import file may not be larger than :max KB.',
    'url_empty' => 'URL cannot be empty.',
    'path_must_start_with_slash' => 'Managed URL paths must start with a slash.',
    'self_redirect' => 'A redirect cannot point to itself.',
    'status_code_invalid' => 'Redirect status code must be one of the configured allowed redirect codes.',
    'regex_required' => 'Regex redirect sources must not be empty.',
    'regex_invalid' => 'Regex redirect sources must be valid PHP regular expressions.',
    'regex_too_long' => 'Regex redirect sources may not be longer than :max characters.',
    'absolute_target_host_not_allowed' => 'Absolute redirect targets to :host are not allowed.',
    'redirect_loop' => 'This redirect would create a redirect loop or chain cycle.',
    'not_found_opportunity_missing' => '404 opportunity was not found.',
    'not_found_opportunity_target_required' => 'A target URL is required to convert a 404 opportunity.',
    'csv_row_error' => 'Row :row: :message',
    'csv_required_field' => 'The :field field is required.',
    'csv_temp_stream_failed' => 'Could not open a temporary CSV stream.',
];

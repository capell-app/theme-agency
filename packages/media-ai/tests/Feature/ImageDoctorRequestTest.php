<?php

declare(strict_types=1);

use Capell\MediaAI\Data\ImageDoctorRequest;

it('accepts every known operation', function (string $operation): void {
    $request = new ImageDoctorRequest(
        operation: $operation,
        instructions: 'Tidy up the image.',
        locale: 'cy',
    );

    expect($request->operation)->toBe($operation)
        ->and($request->locale)->toBe('cy');
})->with(ImageDoctorRequest::OPERATIONS);

it('rejects an operation outside the known set', function (): void {
    expect(static fn (): ImageDoctorRequest => new ImageDoctorRequest(
        operation: 'delete_everything',
        instructions: 'Crafted payload.',
    ))->toThrow(InvalidArgumentException::class);
});

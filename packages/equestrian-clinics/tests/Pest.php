<?php

declare(strict_types=1);

use Capell\EquestrianClinics\Tests\EquestrianClinicsTestCase;

pest()->extend(EquestrianClinicsTestCase::class)->group('equestrian-clinics')->in(__DIR__);

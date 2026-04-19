<?php

namespace App\Service;

interface DashboardInterface
{
    public function getDashboardData(\DateTimeInterface $fecha = null): array;
}

<?php
namespace App\Services;

interface ArchiveServiceInterface
{
    public function archiveSoldedDettes(array $clients): void;
}


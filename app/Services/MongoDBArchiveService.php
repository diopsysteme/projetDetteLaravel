<?php
namespace App\Services;

use App\Models\DetteArchive;

class MongoDBArchiveService implements ArchiveServiceInterface
{
    public function archiveSoldedDettes(array $clients): void
    {
        $today = now()->format('Y_m_d');
        $collectionName = 'dettes_' . $today;

        foreach ($clients as $client) {
            $detteArchive = new DetteArchive();
            $detteArchive->setCollect($collectionName);

            $detteArchive->create([
                'client_id' => $client['id'],
                'nom' => $client['nom'],
                'telephone' => $client['telephone'],
                'dettes' => $client['dettes'],
            ]);
        }
    }
}

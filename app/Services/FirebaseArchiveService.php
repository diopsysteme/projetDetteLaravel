<?php
namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseArchiveService implements ArchiveServiceInterface
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials.key_file'))
            ->withDatabaseUri(config('firebase.database.url'));
            
        $this->database = $factory->createDatabase();
    }

    public function archiveSoldedDettes(array $clients): void
    {
        $today = now()->format('Y_m_d');
        $reference = $this->database->getReference('dettes_' . $today);

        foreach ($clients as $client) {
            $reference->push([
                'client_id' => $client['id'],
                'nom' => $client['nom'],
                'telephone' => $client['telephone'],
                'dettes' => $client['dettes'],
            ]);
        }
    }
}

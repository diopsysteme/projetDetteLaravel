<?php
namespace App\Repository;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseRepository implements ArchiveRepositoryInterface
{
    protected $database;


    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials.key_file')) 
            ->withDatabaseUri(config('firebase.database.url'));

        $this->database = $factory->createDatabase();
    }

    public function store( $data,  $path)
    {
        $this->database->getReference($path)->push($data);
    }


    public function retrieve($filters, $path)
    {
        $query = $this->database->getReference($path);
        $snapshot = $query->getSnapshot();
    
        if ($snapshot->exists()) {
            $values = $snapshot->getValue();
            $filteredData = [];
    
            // Filter by client_id if provided
            if (isset($filters['client_id'])) {
                $clientId = $filters['client_id'];
    
                foreach ($values as $key => $clientData) {
                    if (isset($clientData['client_id']) && $clientData['client_id'] == $clientId) {
                        // If there's a dette_id filter, apply it
                        if (isset($filters['dette_id'])) {
                            $detteId = $filters['dette_id'];
                            if (isset($clientData['dettes'])) {
                                $clientData['dettes'] = array_filter($clientData['dettes'], function ($dette) use ($detteId) {
                                    return isset($dette['dette_id']) && $dette['dette_id'] == $detteId;
                                });
    
                                // Add to the result only if there are matching debts
                                if (!empty($clientData['dettes'])) {
                                    $filteredData[$key] = $clientData;
                                }
                            }
                        } else {
                            // If there's no dette_id filter, add client data
                            $filteredData[$key] = $clientData;
                        }
                    }
                }
            } else {
                // No client_id filter, check for dette_id filter independently
                if (isset($filters['dette_id'])) {
                    $detteId = $filters['dette_id'];
                    foreach ($values as $key => $clientData) {
                        if (isset($clientData['dettes'])) {
                            $clientData['dettes'] = array_filter($clientData['dettes'], function ($dette) use ($detteId) {
                                return isset($dette['dette_id']) && $dette['dette_id'] == $detteId;
                            });
    
                            // Add to the result if there's at least one matching debt
                            if (!empty($clientData['dettes'])) {
                                $filteredData[$key] = $clientData;
                            }
                        }
                    }
                } else {
                    // No filters applied, return all data
                    $filteredData = $values;
                }
            }
    
            return $filteredData;
        }
    
        return [];
    }
    
    
    
    public function retrieveAll($filter){
        $query = $this->database->getReference();
        $debts=[];
        foreach ($query->getChildKeys() as $key){
            $debts[]=$this->retrieve($filter,$key);
        }
        return $debts;
    }

    public function delete($detteId, $path)
    {
        $query = $this->database->getReference($path);
        $snapshot = $query->getSnapshot();
    
        if ($snapshot->exists()) {
            $values = $snapshot->getValue();
            foreach ($values as $key => $clientData) {
                if (isset($clientData['dettes'])) {
                    foreach ($clientData['dettes'] as $index => $dette) {
                        if (isset($dette['dette_id']) && $dette['dette_id'] == $detteId) {
                            unset($clientData['dettes'][$index]);
                            $this->database->getReference($path . '/' . $key)->update(['dettes' => array_values($clientData['dettes'])]);
                            return true;
                        }
                    }
                }
            }
        }
    
        return false;
    }
    
public function deteleByDebtId($id){
    $query = $this->database->getReference();
    foreach ($query->getChildKeys() as $key){
        $this->delete($id,$key);
    }
}
    public function restore( $filters,  $path)
    {
        $document = $this->retrieve($filters, $path);

        if ($document) {
            $documentKey = array_key_first($document);
            $this->delete($documentKey, $path);

            return $document;
        }

        return null;
    }
}

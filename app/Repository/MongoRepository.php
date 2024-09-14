<?php 
namespace App\Repository;

use App\Models\DetteArchive;

class MongoRepository implements ArchiveRepositoryInterface
{
    protected $model;

    public function __construct(DetteArchive $model)
    {
        $this->model = $model;
    }

    /**
     * Store a new archived debt record in MongoDB.
     *
     * @param array $data
     * @param string $collectionName
     */
    public function store(array $data)
    {
        $today = now()->format('Y_m_d');
        $collectionName = 'dettes_' . $today;
        $this->model->setCollect($collectionName);

        $this->model->create($data);
    }

    /**
     * Retrieve a specific archived debt based on filters.
     *
     * @param array $filters
     * @param string $collectionName
     * @return mixed
     */
    public function retrieve($filters, $collectionName)
{
    $this->model->setCollect($collectionName);

    // If no filters are provided, return all documents
    if (empty($filters)) {
        return $this->model->all()->toArray();
    }

    // Prepare the query array
    $query = [];

    // Add client_id filter if provided
    if (isset($filters['client_id'])) {
        $query['client_id'] = $filters['client_id'];
    }

    // Add dette_id filter if provided
    if (isset($filters['dette_id'])) {
        $query['dettes.dette_id'] = $filters['dette_id'];
    }

    // Retrieve documents based on the query
    $results = $this->model->where($query)->get()->toArray();

    // Filter out clients with empty debts if a dette_id filter was applied
    if (isset($filters['dette_id'])) {
        $filteredResults = [];

        foreach ($results as $clientData) {
            if (isset($clientData['dettes'])) {
                // Filter debts for the specific dette_id
                $filteredDebts = array_filter($clientData['dettes'], function ($dette) use ($filters) {
                    return isset($dette['dette_id']) && $dette['dette_id'] == $filters['dette_id'];
                });

                // Include the client in the result if there are matching debts
                if (!empty($filteredDebts)) {
                    $clientData['dettes'] = $filteredDebts;
                    $filteredResults[] = $clientData;
                }
            }
        }

        return $filteredResults;
    }

    return $results;
}


    
    public function retrieveAll($filters){
       $collections= $this->model->listAllCollections();
       $debts=[];
       foreach($collections as $collection){
        $debts[]=$this->retrieve($filters,$collection);
       }
       return $debts;
    }
    public function deleteDebt($detteId, $collectionName)
    {
        $this->model->setCollect($collectionName);
    
        // Récupérez les documents contenant la dette spécifique
        $results = $this->retrieve($detteId, $collectionName);
    
        foreach ($results as $document) {
            // dd($document);
            $clientId = $document['client_id'];
    
            // Suppression de la dette spécifique dans le document
            $this->model->where('client_id', $clientId)
                        ->update([
                            '$pull' => ['dettes' => ['dette_id' => $detteId]]
                        ]);
        }
    }
    
    public function deleteByIdDebt($id){
        $collections= $this->model->listAllCollections();
        foreach($collections as $collection){
         $this->deleteDebt($id,$collection);
        }
    }

    /**
     * Delete an archived debt by its ID.
     *
     * @param string $id
     * @param string $collectionName
     */
    public function delete( $id,  $collectionName=null )
    {
        $this->model->setCollect($collectionName);
        $this->model->where('_id', $id)->delete();
    }

    /**
     * Restore an archived debt based on filters.
     * Deletes the document after restoration.
     *
     * @param array $filters
     * @param string $collectionName
     * @return mixed|null
     */
    public function restore( $filters,  $collectionName)
    {
        $document = $this->retrieve($filters, $collectionName);


        if ($document) {
            // Handle the restoration logic, e.g., inserting it back into the main database
            // For example: MainDatabase::create($document->toArray());

            // Delete the document after restoring it
            $this->delete($document->_id, $collectionName);

            return $document;
        }

        return null;
    }
}

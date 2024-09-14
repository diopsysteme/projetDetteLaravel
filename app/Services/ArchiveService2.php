<?php
namespace App\Services;

use App\Repository\MongoRepository;
use App\Repository\FirebaseRepository;


class ArchiveService2
{
    protected $firebaseRepo;
    protected $mongoRepo;

    public function __construct(FirebaseRepository $firebaseRepo, MongoRepository $mongoRepo)
    {
        $this->firebaseRepo = $firebaseRepo;
        $this->mongoRepo = $mongoRepo;
    }

    public function getArchivedDebts($filters = [])
    {
        $firebaseData = $this->firebaseRepo->retrieveAll($filters);
        $mongoData = $this->mongoRepo->retrieveAll($filters);
        if(!$mongoData|| !$firebaseData){
            return !$mongoData?$firebaseData:$mongoData;
        }
        return array_merge($firebaseData, $mongoData);
    }

    public function restoreDebtById($detteId)
    {
        $debt = $this->firebaseRepo->getDebtById($detteId) ?? $this->mongoRepo->getDebtById($detteId);

        if ($debt) {
            $this->restoreToMainDatabase($debt);

            $this->firebaseRepo->deleteDebtById($detteId);
            $this->mongoRepo->deleteDebtById($detteId);
        }
    }
    public function restoreDebtsByDate($date)
    {
        $firebaseDebts = $this->firebaseRepo->getDebtsByDate($date);
        $mongoDebts = $this->mongoRepo->getDebtsByDate($date);

        $allDebts = array_merge($firebaseDebts, $mongoDebts);

        foreach ($allDebts as $debt) {
            $this->restoreToMainDatabase($debt);

            $this->firebaseRepo->deleteDebtById($debt['dette_id']);
            $this->mongoRepo->deleteDebtById($debt['dette_id']);
        }
    }
    public function deleteDebtById($id){
        $this->mongoRepo->deleteByIdDebt($id);
        $this->firebaseRepo->deteleByDebtId($id);
    }
    protected function restoreToMainDatabase($debt)
    {
        // Implement your restoration logic here
    }
}

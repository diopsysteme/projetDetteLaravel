<?php
// app/Repositories/DemandeRepository.php

namespace App\Repository;

use App\Models\Demande;
use Illuminate\Support\Facades\DB;
use App\Jobs\NotifyBoutiquiersOfNewDemande;

class DemandeRepository implements DemandeRepositoryInterface
{
    public function create(array $data)
    {
        return Demande::create($data);
    }

    public function find($id)
    {
        return Demande::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $demande = $this->find($id);
        $demande->update($data);
        return $demande;
    }

    public function delete($id)
    {
        $demande = $this->find($id);
        $demande->delete();
    }
    public function createDemandeWithArticles($clientId, $totalMontant, $validatedArticles)
    {
        DB::beginTransaction();

        try {
            $demande = Demande::create([
                'client_id' => $clientId,
                'montant' => $totalMontant,
                'etat' => 'en_attente',
                'date_echeance' => null,
            ]);
            foreach ($validatedArticles as $validatedArticle) {
                $demande->articles()->attach($validatedArticle['article_id'], [
                    'quantite' => $validatedArticle['quantite'],
                    'dispo' => $validatedArticle['montant'],
                ]);
            }

            DB::commit();
            NotifyBoutiquiersOfNewDemande::dispatch($demande);
            return $demande;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

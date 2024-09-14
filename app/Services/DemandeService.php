<?php 

// app/Services/DemandeService.php

namespace App\Services;

use App\Models\Article;
use App\Models\Demande;
use Illuminate\Support\Facades\Auth;
use App\Repository\DemandeRepositoryInterface;

class DemandeService implements DemandeServiceInterface
{
    protected $demandeRepository;

    public function __construct(DemandeRepositoryInterface $demandeRepository)
    {
        $this->demandeRepository = $demandeRepository;
    }
    public function handleCreateDemande(array $data)
    {
        $clientId = auth()->id();
        $clientId = 182;
        $totalMontant = 0;
        $validatedArticles = [];
        $invalidArticles = [];

        foreach ($data['articles'] as $articleData) {
            $article = Article::find($articleData['id']);

            if (!$article || $articleData['qte_vente'] <= 0) {
                $invalidArticles[] = [
                    'article_id' => $articleData['id'],
                    'message' => !$article ? 'Article non trouvé' : 'Quantité invalide',
                ];
                continue;
            }

            if ($article->stock >= $articleData['qte_vente']) {
                $validatedArticles[] = [
                    'article_id' => $article->id,
                    'quantite' => $articleData['qte_vente'],
                    'montant' => 1,
                ];
                $totalMontant += $article->prix * $articleData['qte_vente'];
            } else {
                $validatedArticles[] = [
                    'article_id' => $article->id,
                    'quantite' => $articleData['qte_vente'],
                    'montant' => 0,
                ];
            }
        }

        $demande = $this->demandeRepository->createDemandeWithArticles($clientId, $totalMontant, $validatedArticles);

        return [
            'demande' => $demande,
            'invalid_articles' => $invalidArticles,
        ];
    }
    public function createDemande(array $data)
    {
        return $this->demandeRepository->create($data);
    }

    public function updateDemande($id, array $data)
    {
        return $this->demandeRepository->update($id,  $data);
    }

    public function getDemandeById($id){
        return $this->demandeRepository->find($id);
    }
}

<?php
namespace App\Http\Controllers;
use App\Models\Dette;
use App\Models\Client;
use App\Models\DetteArchive;
use Illuminate\Http\Request;
use App\Facades\ArchiveFacade;
use Illuminate\Support\Facades\Log;
use App\Facades\SmsServiceFacade as SmsService;

class DetteArchiveController extends Controller
{
    public function show()
    {
    //     $clients = Client::with('dettes')->get();

    //     // Parcourir chaque client
    //     foreach ($clients as $client) {
    //         // Filtrer les dettes non payées
    //         $totalDettes = $client->dettes->filter(function ($dette) {
    //             return $dette->montant_restant > 0; // Utilise l'attribut calculé pour le montant restant
    //         })->sum('montant_restant');

    //         // Vérifie s'il y a des dettes à rappeler
    //         if ($totalDettes > 0) {
    //             // Message à envoyer
    //             $nom=$client->user?$client->user->prenom.' '.$client->user->nom: $client->surnom;
    //             $message = "Bonjour {$nom}, vous avez un total de {$totalDettes} FCFA de dettes chez DIOP E-SHOP.";
    //         Log::info($message);
    //         // Envoi du SMS
    //         // SmsService::sendMessage('DIOP E-SHOP', $client->phone_number, $message);
    //     }
    // }

// SmsService::sendMessage('DIOP E-SHOP', '+221785342948', 'Bonjour, voici votre message.');

        $dettes = Dette::all()
            ->load(['client', 'payement', 'articles'])
            ;

        $clients = $dettes->filter(function ($dette) {
            return $dette->etat_solde;
        })->groupBy('client_id') 
        ->map(function ($dettes) {
            $client = $dettes->first()->client;

            return [
                'id' => $client->id,
                'nom' =>$client->user? $client->user->prenom." ".$client->user->nom:$client->surnom,
                'telephone' => $client->telephone,
                'dettes' => $dettes->map(function ($dette) {
                    return [
                        'dette_id' => $dette->id,
                        'montant_dette' => $dette->montant,
                        'payments' => $dette->payement->map(function ($payment) {
                            return [
                                'payment_id' => $payment->id,
                                'montant' => $payment->montant,
                                'date' => $payment->created_at->toDateString(),
                            ];
                        })->toArray(),
                        'articles' => $dette->articles->map(function ($article) {
                            return [
                                'article_id' => $article->id,
                                'libelle' => $article->libelle,
                                'prix_vente' => $article->pivot->prix_vente,
                                'quantite' => $article->pivot->qte_vente,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];
        })->values()->toArray();
        ArchiveFacade::archiveSoldedDettes($clients);
    DetteArchive::create([
        'field1' => 'value1',
        'field2' => 'value2',
    ]);
       return  [

           'dette' => DetteArchive::all(),
       ];
   }

}
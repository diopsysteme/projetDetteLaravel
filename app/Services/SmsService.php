<?php

namespace App\Services;

use Infobip\Configuration;
use Infobip\Api\SmsApi;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;

class SmsService implements SmsServiceInterface
{
    private $smsApi;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $configuration = new Configuration(
            host: $baseUrl,
            apiKey: $apiKey
        );
        $this->smsApi = new SmsApi($configuration);
    }
    public function sendMessage(string $from, string $to, string $messageText): void
{
    // Créer la destination avec le numéro de téléphone
    $destination = new SmsDestination($to); // Passe directement le numéro en paramètre du constructeur

    // Créer le message avec son contenu textuel et la destination
    $message = new SmsTextualMessage([$destination]); // Le constructeur attend un tableau de `SmsDestination`
    $message->setFrom($from); // Définir l'expéditeur
    $message->setText($messageText); // Définir le contenu du message

    // Créer la requête avancée pour l'envoi du message
    $request = new SmsAdvancedTextualRequest([$message]); // Le constructeur attend un tableau de `SmsTextualMessage`

    try {
        // Envoyer le message via l'API Infobip
        $response = $this->smsApi->sendSmsMessage($request);
        echo "Message envoyé avec succès : " . $response->getMessages()[0]->getStatus()->getName();
    } catch (\Exception $e) {
        // Gérer l'exception et afficher un message d'erreur
        echo "Erreur lors de l'envoi du message : " . $e->getMessage();
    }
}

    
    
    // public function sendMessage(string $from, string $to, string $messageText): void
    // {
    //     // Créer la destination du message
    //     $destination = new SmsDestination(
    //          $to
    //     );

    //     // Créer le message avec son contenu textuel
    //     $message = new SmsTextualMessage([
    //         'from' => $from,
    //         'destinations' => [$destination],
    //         'text' => $messageText
    //     ]);

    //     // Créer la requête avancée pour l'envoi du message
    //     $request = new SmsAdvancedTextualRequest([
    //         'messages' => [$message]
    //     ]);

    //     try {
    //         // Utilisation de SmsAdvancedTextualRequest dans la méthode sendSmsMessage
    //         $response = $this->smsApi->sendSmsMessage($request);
    //         echo "Message envoyé avec succès : " . $response->getMessages()[0]->getStatus()->getName();
    //     } catch (\Exception $e) {
    //         // Gérer l'exception
    //         echo "Erreur lors de l'envoi du message : " . $e->getMessage();
    //     }
    // }
}

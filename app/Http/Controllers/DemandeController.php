<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DemandeServiceInterface;

class DemandeController extends Controller
{
    protected $demandeService;

    public function __construct(DemandeServiceInterface $demandeService)
    {
        $this->demandeService = $demandeService;
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $demande = $this->demandeService->handleCreateDemande($data);
        return response()->json($demande, 201);
    }
}

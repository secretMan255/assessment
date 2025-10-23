<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;              
use App\Http\Requests\NoteStoreRequest;
use App\Http\Requests\NoteUpdateRequest;
use App\Http\Requests\NoteIndexRequest;
use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    private NoteService $svc;

    public function __construct(NoteService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(NoteIndexRequest  $request): JsonResponse
    {
         $notes = $this->svc->list($request->user(), $request->filters());
         return response()->json($notes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NoteStoreRequest $request)
    {
        $note = $this->svc->create($request->user(), $request->validated()); 
        return response()->json($note, 201);              
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NoteUpdateRequest $request, Note $note)
    {
        $this->authorizeNote($note);
        $note = $this->svc->update($note, $request->validated());
        return response()->json($note);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $this->authorizeNote($note);
        $this->svc->delete($note);                                          
        return response()->json(['deleted' => true]);
    }

    private function authorizeNote(Note $note): void
    {
        abort_unless($note->user_id === Auth::id(), 403, 'Forbidden');        
    }
}

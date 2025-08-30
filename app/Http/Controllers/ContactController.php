<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:contatos.index');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $myContacts = Contact::getByUser(Auth::id());
        $allContacts = Contact::getAllWithUsers();
        
        return view('contacts.index', compact('myContacts', 'allContacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contacts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()
                ->route('contacts.create')
                ->withErrors($validator)
                ->withInput();
        }

        $contact = Contact::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'whatsapp' => $request->whatsapp,
            'notes' => $request->notes
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'contact' => $contact,
                'message' => 'Contato criado com sucesso!'
            ]);
        }

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contato criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'id' => $contact->id,
                'name' => $contact->name,
                'whatsapp' => $contact->whatsapp,
                'notes' => $contact->notes,
                'user_id' => $contact->user_id,
                'created_at' => $contact->created_at,
                'updated_at' => $contact->updated_at
            ]);
        }
        
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        // Verificar se o usuário pode editar este contato
        if ($contact->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para editar este contato.');
        }

        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        // Verificar se o usuário pode editar este contato
        if ($contact->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para editar este contato.'
                ], 403);
            }
            
            abort(403, 'Você não tem permissão para editar este contato.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()
                ->route('contacts.edit', $contact)
                ->withErrors($validator)
                ->withInput();
        }

        $contact->update([
            'name' => $request->name,
            'whatsapp' => $request->whatsapp,
            'notes' => $request->notes
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'contact' => $contact,
                'message' => 'Contato atualizado com sucesso!'
            ]);
        }

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contato atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        // Verificar se o usuário pode excluir este contato
        if ($contact->user_id !== Auth::id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para excluir este contato.'
                ], 403);
            }
            
            abort(403, 'Você não tem permissão para excluir este contato.');
        }

        $contact->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Contato excluído com sucesso!'
            ]);
        }

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contato excluído com sucesso!');
    }

    /**
     * Buscar contatos via AJAX
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $userId = Auth::id();
        
        $contacts = Contact::where('user_id', $userId)
            ->where('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->get();

        return response()->json($contacts);
    }
}

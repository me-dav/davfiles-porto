<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JournalEntry;
use Illuminate\Validation\Rule;

class JournalController extends Controller
{
    public function index()
    {
        $entries = JournalEntry::orderBy('created_at')->paginate(15);
        return view('dashboard.journal.index', compact('entries'));
    }

    public function create()
    {
        return view('dashboard.journal.create');
    }

    public function store(Request $Request)
    {
        $data = $Request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:journal_entries,slug',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
            ],
            'type' => ['required', Rule::in(['exploration', 'server-notes', 'hiking', 'ui-thoughts', 'learning-log'])],
            'hero_image_url' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'tags' => ['nullable', 'string'],   // comma-separated
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        $data['tags'] = $this->parseTags($data['tags'] ?? '');

        if($data['status']=='published'){
            $data['published_at'] = now();
        }

        JournalEntry::create($data);

        return redirect()
            ->route('dashboard.journal.index')
            ->with('success','Entry "'.$data['title'].'" berhasil dibuat.');
    }

    public function edit(JournalEntry $journal){
        return view('dashboard.journal.edit',['entry'=>$journal]);
    }

    public function update(Request $request, JournalEntry $journal)
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'slug'           => ['required', 'string', 'max:255',
                                 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                                 Rule::unique('journal_entries', 'slug')->ignore($journal->id)],
            'type'           => ['required', Rule::in(['exploration','server-notes','hiking','ui-thoughts','learning-log'])],
            'hero_image_url' => ['nullable', 'string', 'max:500'],
            'body'           => ['nullable', 'string'],
            'tags'           => ['nullable', 'string'],
            'status'         => ['required', Rule::in(['draft', 'published'])],
        ]);
 
        $data['tags'] = $this->parseTags($data['tags'] ?? '');
 
        // Set published_at hanya pertama kali publish
        if ($data['status'] === 'published' && ! $journal->published_at) {
            $data['published_at'] = now();
        }
 
        $journal->update($data);
 
        return redirect()
            ->route('dashboard.journal.index')
            ->with('success', 'Entry "' . $journal->title . '" berhasil diupdate.');
    }
 
    public function destroy(JournalEntry $journal)
    {
        $title = $journal->title;
        $journal->delete();
 
        return redirect()
            ->route('dashboard.journal.index')
            ->with('success', 'Entry "' . $title . '" berhasil dihapus.');
    }
 
    private function parseTags(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->toArray();
    }

}

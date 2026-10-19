<?php

// JournalController
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    use ApiResponse;

    public function index(Request $r)
    {
        $r->validate([
            'type' => 'nullable|in:exploration,server,hiking,ui_thoughts,learning_log',
            'tag' => 'nullable|string|max:40',
            'q' => 'nullable|string|max:80',
            'per' => 'nullable|integer|min:1|max:50',
        ]);

        $p = JournalEntry::published()
            ->when($r->type, fn($q, $v) => $q->where('type', $v))
            ->when($r->tag, fn($q, $v) => $q->whereJsonContains('tags', $v))
            ->when($r->q, fn($q, $v) => $q->where('title', 'ilike', '%' . addcslashes($v, '%_') . '%'))
            ->orderByDesc('published_at')
            ->paginate($r->integer('per', 12));

        return $this->ok([
            'entries' => JournalEntryResource::collection($p->items())->resolve(),
            'meta' => ['total' => $p->total(), 'per_page' => $p->perPage(), 'current_page' => $p->currentPage()],
        ], 'Journal fetched');
    }

    public function show(string $slug)
    {
        $e = JournalEntry::published()->where('slug', $slug)->first();
        if (!$e) {
            return $this->fail('Entry not found', 404);
        }

        return $this->ok([
            'entry' => array_merge(
                (new JournalEntryResource($e))->resolve(),
                ['body_markdown' => $e->body, 'body_html' => $e->bodyHtml()],
            ),
        ], 'Entry fetched');
    }
}
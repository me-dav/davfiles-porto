<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJournalRequest;
use App\Http\Resources\JournalResource;
use App\Models\JournalEntry;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    use ApiResponse;

    // ── Public endpoints ────────────────────────────────────────────────────

    /**
     * GET /api/v1/journal?tag=server&type=exploration&page=1&per=9
     *
     * Paginated list of published entries.
     * Supports filtering by ?tag= and ?type=
     */
    public function index(Request $request): JsonResponse
    {
        $per = (int) min($request->get('per', 9), 50);

        $query = JournalEntry::published()
            ->orderByDesc('published_at');

        // Filter by type
        if ($type = $request->get('type')) {
            $validTypes = ['exploration', 'server-notes', 'hiking', 'ui-thoughts', 'learning-log'];
            if (in_array($type, $validTypes)) {
                $query->where('type', $type);
            }
        }

        // Filter by tag (JSON column)
        if ($tag = $request->get('tag')) {
            $query->whereJsonContains('tags', $tag);
        }

        $entries = $query->paginate($per);

        return $this->success([
            'entries' => JournalResource::collection($entries->items()),
            'meta'    => [
                'total'        => $entries->total(),
                'per_page'     => $entries->perPage(),
                'current_page' => $entries->currentPage(),
                'last_page'    => $entries->lastPage(),
            ],
        ], 'Journal entries fetched');
    }

    /**
     * GET /api/v1/journal/{slug}
     *
     * Single published entry including full body.
     */
    public function show(string $slug): JsonResponse
    {
        $entry = JournalEntry::published()
            ->where('slug', $slug)
            ->first();

        if (! $entry) {
            return $this->error('Entry not found', 404);
        }

        return $this->success(
            ['entry' => new JournalResource($entry)],
            'Entry fetched'
        );
    }

    // ── Admin endpoints ──────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/journal
     *
     * List ALL entries including drafts (admin only).
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $per = (int) min($request->get('per', 50), 100);

        $entries = JournalEntry::orderByDesc('created_at')->paginate($per);

        return $this->success([
            'entries' => JournalResource::collection($entries->items()),
            'meta'    => [
                'total'        => $entries->total(),
                'per_page'     => $entries->perPage(),
                'current_page' => $entries->currentPage(),
                'last_page'    => $entries->lastPage(),
            ],
        ], 'All journal entries fetched');
    }

    /**
     * POST /api/v1/admin/journal
     */
    public function store(StoreJournalRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Auto-set published_at when publishing for the first time
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $entry = JournalEntry::create($data);

        return $this->success(
            ['entry' => new JournalResource($entry)],
            'Entry created',
            201
        );
    }

    /**
     * PUT /api/v1/admin/journal/{id}
     */
    public function update(StoreJournalRequest $request, int $id): JsonResponse
    {
        $entry = JournalEntry::find($id);

        if (! $entry) {
            return $this->error('Entry not found', 404);
        }

        $data = $request->validated();

        // Set published_at only when first publishing
        if ($data['status'] === 'published' && ! $entry->published_at) {
            $data['published_at'] = now();
        }

        $entry->update($data);

        return $this->success(
            ['entry' => new JournalResource($entry->fresh())],
            'Entry updated'
        );
    }

    /**
     * DELETE /api/v1/admin/journal/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $entry = JournalEntry::find($id);

        if (! $entry) {
            return $this->error('Entry not found', 404);
        }

        $entry->delete();

        return $this->success(null, 'Entry deleted');
    }
}
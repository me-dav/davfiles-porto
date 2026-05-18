<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ToolboxItem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ToolboxController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/toolbox
     *
     * Returns all toolbox items ordered by 'order' column.
     */
    public function index(): JsonResponse
    {
        $tools = ToolboxItem::orderBy('order')->get();

        return $this->success(
            ['tools' => $tools->map(fn ($t) => [
                'id'       => $t->id,
                'name'     => $t->name,
                'logo_url' => $t->logo_url,
                'order'    => $t->order,
            ])],
            'Toolbox fetched'
        );
    }
}
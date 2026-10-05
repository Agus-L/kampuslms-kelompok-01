<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Base PaginatedResourceCollection
 *
 * Menghasilkan struktur meta pagination persis sesuai kontrak spek-week6.md:
 * {
 *   "data": [ ... ],
 *   "meta": {
 *     "current_page": 1,
 *     "last_page": 5,
 *     "total": 47
 *   }
 * }
 */
class PaginatedResourceCollection extends ResourceCollection
{
    /**
     * Sesuaikan informasi pagination agar hanya menyertakan current_page, last_page, dan total.
     * Menghilangkan tautan 'links' di root dan properti berlebih di dalam 'meta'.
     */
    public function paginationInformation($request, $paginated, $default): array
    {
        return [
            'meta' => [
                'current_page' => (int) $paginated['current_page'],
                'last_page'    => (int) $paginated['last_page'],
                'total'        => (int) $paginated['total'],
            ],
        ];
    }
}

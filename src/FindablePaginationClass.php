<?php

namespace Findable;

use Illuminate\Pagination\LengthAwarePaginator;

class FindablePaginationClass extends LengthAwarePaginator
{
    public function __construct($items, $total, $perPage, $currentPage, $pageName = 'page')
    {
        parent::__construct($items, $total, $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);

        // Initialize your custom properties if needed
    }
}

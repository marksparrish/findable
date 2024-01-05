<?php

namespace Findable\Traits;

use Illuminate\Pagination\Paginator;
use Findable\PaginateResults;

trait FindablePaginationTrait
{
    private $page = 1;
    private $pageName = 'page';

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function resolveCurrentPage()
    {
        return Paginator::resolveCurrentPage($this->pageName);
    }

    public function createPaginator($items, $total, $perPage)
    {
        return new PaginateResults($items, $total, $perPage, $this->getPage());
    }

    // ... other pagination methods ...
}

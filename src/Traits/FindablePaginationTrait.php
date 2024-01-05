<?php

namespace Findable\Traits;

use Illuminate\Pagination\Paginator;
use Findable\PaginateResults;

trait FindablePaginationTrait
{
    private $paginationPage = 1;
    private $pageName = 'page';

    public function setPaginationPage($page)
    {
        $this->paginationPage = $page;
    }

    public function getPaginationPage()
    {
        return $this->paginationPage;
    }

    public function resolveCurrentPage()
    {
        return Paginator::resolveCurrentPage($this->pageName);
    }

    public function createPaginator($items, $total, $perPage)
    {
        return new PaginateResults($items, $total, $perPage, $this->getPaginationPage());
    }

    // ... other pagination methods ...
}

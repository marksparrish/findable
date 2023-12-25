<?php

namespace App\Elastic;

use Illuminate\Pagination\LengthAwarePaginator;

/*

    This class is used to return the results of the query to the controller.
    It is used in the paginate() method in the Engine class.
    It is a wrapper for the LengthAwarePaginator class and adds the raw, aggregations, and params properties.
*/

class PaginateResults extends LengthAwarePaginator
{
    public $raw;
    public $aggregations;
    public $params;

    public function __construct($items, $total, $perPage, $currentPage = null, $options = [])
    {
        parent::__construct($items, $total, $perPage, $currentPage, $options);
    }
}

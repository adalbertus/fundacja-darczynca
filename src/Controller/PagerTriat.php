<?php
namespace App\Controller;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

trait PagerTriat
{
    protected function getPager(QueryBuilder $queryBuilder, Request $request, int $page = 1, int $pageSize = 30): Pagerfanta
    {
        $pageGet = $request->query->get('page', $page);
        $pageSizeGet = $request->query->get('pagesize', $pageSize);

        $adapter = new QueryAdapter($queryBuilder);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $pageGet,
            $pageSizeGet
        );

        return $pager;
    }
}
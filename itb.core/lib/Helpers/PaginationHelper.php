<?php

namespace Itb\Core\Helpers;

class PaginationHelper
{
    public static function toArray(?\CIBlockResult $nav): array
    {
        if (!$nav) {
            return [
                'pages'              => [],
                'pageSize'           => 0,
                'currentPage'        => 1,
                'pageCount'          => 0,
                'paginationUrlParam' => '',
            ];
        }

        return [
            'pages' => self::getPages($nav->NavPageNomer, $nav->NavPageCount),
            'pageSize' => (int)$nav->NavPageSize,
            'currentPage' => (int)$nav->NavPageNomer,
            'pageCount' => (int)$nav->NavPageCount,
            'paginationUrlParam' => 'PAGEN_' . $nav->NavNum,
        ];
    }

    public static function getPages(int $currentPage, int $pageCount): array
    {
        // Количество отображаемых страниц
        $pageWindow = 5;

        if ($currentPage > floor($pageWindow / 2) + 1 && $pageCount > $pageWindow) {
            $startPage = $currentPage - floor($pageWindow / 2);
        } else {
            $startPage = 1;
        }

        if ($currentPage <= $pageCount - floor($pageWindow / 2) && $startPage + $pageWindow - 1 <= $pageCount) {
            $endPage = $startPage + $pageWindow - 1;
        } else {
            $endPage = $pageCount;
            if ($endPage - $pageWindow + 1 >= 1) {
                $startPage = $endPage - $pageWindow + 1;
            }
        }

        $pages = [];
        for ($i = $startPage; $i <= $endPage; $i++) {
            $pages[] = [
                'pageNumber' => $i,
                'isSelected' => (int)$currentPage == $i,
            ];
        }
        return $pages;
    }
}

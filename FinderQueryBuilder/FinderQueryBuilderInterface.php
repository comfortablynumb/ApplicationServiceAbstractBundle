<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\FinderQueryBuilder;

interface FinderQueryBuilderInterface
{
    public function create(array $filters = array(), $start = null, $limit = null, $orderBy = null, $orderType = null, $onlyCount = false, $qb = null);
}

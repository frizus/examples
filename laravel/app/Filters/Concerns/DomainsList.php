<?php
namespace App\Filters\Concerns;

use App\Repositories\TargetDomainRepository;

trait DomainsList
{
    public function domainsList()
    {
        $list = ['' => 'Все домены'];
        $targetDomainRepository = app()->make(TargetDomainRepository::class);
        foreach ($targetDomainRepository->all() as $id => $targetDomain) {
            $list[$id] = e($targetDomain['name'] . ' (' . $targetDomain['host'] . ')', false);
        }
        return $this->countForFilter->addCounts('domain', $list);
    }
}

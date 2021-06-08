<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\IpCustomer;

/**
 * Class IpCustomerRepository
 *
 * @package \T2G\Common\Repository
 */
class IpCustomerRepository extends AbstractEloquentRepository
{
    const DEFAULT_PER_PAGE = 100;

    /**
     * @return string
     */
    public function model(): string
    {
        return IpCustomer::class;
    }

    public function exists($ip)
    {
        $query = $this->query();
        $query->where('ip', $ip);
        $exited = $query->get()->count() > 0;
        return $exited;
    }

    public function createIfNotExists($data)
    {
        $ip = $data['ip'];
        if ($this->exists($ip)) {
            return true;
        }

        $this->create($data);
    }

    public function paginate($limit = self::DEFAULT_PER_PAGE)
    {
        $query = $this->query()->select('ip');
        $result = $query->where('status', 1)
            ->orderBy('updated_at', 'desc')
            ->paginate($limit);
        return $result;
    }
}

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

    public function createOrUpdate($data)
    {
        try {
            $ip = $data['ip'];
            $hwid = $data['hwid'];
            $query = $this->query();
            $query->where('hwid', $hwid)->orWhere('ip', $ip);
            $exiteds = $query->get();

            // Cleanup if dupliacte
            if ($exiteds->count() > 1) {
                foreach ($exiteds as $item) {
                    $item->forceDelete();
                }
                $exiteds = collect([]);
            }

            if ($exiteds->count() == 1) {
                $current = $exiteds->first();
                if ($current->ip != $ip) {
                    $this->update($data, $current->ip);
                }
                return true;
            }
            
            $data["created_at"] = now();
            $this->create($data);
            return true;
        } catch (\Exception $ex) {
            throw $ex;
        } finally {
            return false;
        }
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

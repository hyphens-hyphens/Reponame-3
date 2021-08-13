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

            // Check exactly same
            $exitedQuery = $this->query();
            $exited = $exitedQuery->where('hwid', $hwid)->where('ip', $ip);
            if($exited->count() > 0){
                return true;
            }

            // mutilple (4) ip for 1 hwid 
            $query = $this->query();
            $query->where('hwid', $hwid);
            $query->orderBy('updated_at', 'asc');
            $exiteds = $query->get();

            if ($exiteds->count() > 3) {
                $current = $exiteds->first();
                if ($current->ip != $ip) {
                    $this->update($data, $current->id);
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
            ->groupBy('ip')
            ->paginate($limit);
        return $result;
    }
}

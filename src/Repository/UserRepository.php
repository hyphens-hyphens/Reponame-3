<?php

namespace T2G\Common\Repository;

use Illuminate\Database\DatabaseManager;
use T2G\Common\Models\AbstractUser;

/**
 * Class UserRepository
 */
class UserRepository extends AbstractEloquentRepository
{

    /**
     * @return string
     */
    public function model(): string
    {
        $userModelClass = config('t2g_common.models.user_model_class');

        return $userModelClass;
    }

    /**
     * @return int
     */
    public function getTodayRegistered()
    {
        $startOfToday = date('Y-m-d 00:00:00');
        $query = $this->query()->where('created_at', '>', $startOfToday);

        return $query->count();
    }

    /**
     * @param $data
     *
     * @return AbstractUser|null
     * @throws \Throwable
     */
    public function registerUser(array $data)
    {
        $data['name'] = strtolower($data['name'] ?? '');
        /** @var AbstractUser $user */
        $user = $this->makeModel();
        $user->fill(array_only($data, ['name', 'phone', 'email']));
        $this->updatePassword($user, $data['password'] ?? '');

        return $user;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param                                 $password
     */
    public function updatePassword(AbstractUser $user, $password)
    {
        $hasher = app(\Illuminate\Contracts\Hashing\Hasher::class);
        $user->password = $hasher->make($password);
        $user->raw_password = base64_encode($password);
        $user->save();
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param                                 $password2
     */
    public function updatePassword2(AbstractUser $user, $password2)
    {
        $user->password2 = base64_encode($password2);
        $user->save();
    }

    /**
     * return auto complete results (with data format that match with select2) ['id' => 'text']
     * @param     $term
     * @param int $limit
     *
     * @return array
     */
    public function getAutoCompleteUsers($term, $limit = 10)
    {
        $query = $this->query();
        $query->select(['id', 'name as text'])
            ->where('name', 'LIKE', "{$term}%")
            ->orderBy('name', 'ASC')
            ->limit($limit)
        ;

        return $query->get()->toArray();
    }

    /**
     * @param $fromDate
     * @param $toDate
     *
     * @return array
     */
    public function getUserRegisteredReport($fromDate, $toDate)
    {
        /** @var DatabaseManager $db */
        $db = app(DatabaseManager::class);
        $fromDate = strtotime($fromDate);
        $toDate = strtotime($toDate) + (24*3600) - 1;
        $data = $db->table('users')->selectRaw("DATE_FORMAT(created_at, '%d-%m') as `date`, CONCAT(COALESCE(utm_campaign, ''), '|', COALESCE(utm_medium, ''), '|', COALESCE(utm_source, '')) as `cid`, DATE_FORMAT(created_at, '%m-%d') as ordered_date, COUNT(id) as `total`")
            ->whereRaw("UNIX_TIMESTAMP(CONVERT_TZ(created_at, '+07:00', '+00:00')) BETWEEN {$fromDate} AND $toDate")
            ->groupBy('date', 'ordered_date', 'cid')
            ->orderByRaw("ordered_date ASC, total DESC")
            ->get()
        ;
        $reportByDate = [];
        $campaigns = [];
        foreach ($data as $item) {
            @list($campaign, $medium, $source) = explode('|', $item->cid);
            if (!$campaign) $campaign = 'not-set';
            if (!$medium) $medium = 'not-set';
            if (!$source) $source = 'not-set';
            if (!isset($reportByDate[$item->date])) {
                $reportByDate[$item->date] = [
                    'total' => 0,
                    'details' => []
                ];
            }
            $group = $source . "|" . $medium;
            $reportByDate[$item->date]['details']["{$campaign}|$group"] = $item->total;
            $reportByDate[$item->date]['total'] += $item->total;
            if (!isset($campaigns[$campaign]) || !in_array($group, $campaigns[$campaign])) {
                $campaigns[$campaign][] = $group;
            }
        }

        return [
            $reportByDate,
            $campaigns
        ];
    }
}
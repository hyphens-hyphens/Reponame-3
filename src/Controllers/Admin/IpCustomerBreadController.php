<?php

namespace T2G\Common\Controllers\Admin;

use Illuminate\Http\Request;
use T2G\Common\Controllers\Admin\BaseVoyagerController;
use T2G\Common\Models\IpCustomer;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Facades\Voyager;


/**
 * Class PaymentAdminController
 *
 * @package \T2G\Common\Http\Controllers\Admin
 */
class IpCustomerBreadController extends BaseVoyagerController
{

    protected $searchable = [

    ];

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        $arrIpCustomer = explode(',',$request->ip);
        foreach ($arrIpCustomer as $IpCustomer) {
            $checkRegexIpv4 = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $IpCustomer);
            if ($checkRegexIpv4) {
                $IpCustomer =  ipCustomer::updateOrCreate([
                    'ip' => $IpCustomer,
                ]);
                $IpCustomer->note = $request->note;
                $IpCustomer->save();
            }else {
                return back()->withErrors(sprintf('IP %s bạn nhập không phải định dạng IPV4',$IpCustomer));
            }
        }
        return redirect()
            ->route("voyager.{$dataType->slug}.index")
            ->with([
                'message'    => __('voyager::generic.successfully_added_new')." {$dataType->display_name_singular}",
                'alert-type' => 'success',
            ]);

    }

    /**
     * @param  Request  $request
     * @param $id
     * @return mixed
     */
    public function destroy(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('delete', app($dataType->model_name));

        // Init array of IDs
        $ids = [];
        if (empty($id)) {
            // Bulk delete, get IDs from POST
            $ids = explode(',', $request->ids);
        } else {
            // Single item delete, get ID from URL
            $ids[] = $id;
        }
        foreach ($ids as $id) {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
            $this->changeStatus($id);
            $this->cleanup($dataType, $data);
        }

        $displayName = count($ids) > 1 ? $dataType->display_name_plural : $dataType->display_name_singular;

        $res = $data->destroy($ids);
        $data = $res
            ? [
                'message'    => __('voyager::generic.successfully_deleted')." {$displayName}",
                'alert-type' => 'success',
            ]
            : [
                'message'    => __('voyager::generic.error_deleting')." {$displayName}",
                'alert-type' => 'error',
            ];

        if ($res) {
            event(new BreadDataDeleted($dataType, $data));
        }

        return redirect()->route("voyager.{$dataType->slug}.index")->with($data);
    }

    /**
     * @param $id
     */
    public function changeStatus($id)
    {
        $ip = IpCustomer::findOrFail($id);
        $ip->status = !boolval($ip->status);
        $ip->save();
    }

}

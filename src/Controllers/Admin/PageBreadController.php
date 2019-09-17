<?php

namespace T2G\Common\Controllers\Admin;

use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

/**
 * Class PagesBreadController
 *
 * @package T2G\Common\Controllers\Admin
 */
class PageBreadController extends VoyagerBaseController
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = voyager()->model('DataType')->where('slug', '=', $slug)->first();

        $relationships = $this->getRelationships($dataType);

        /** @var DatabaseManager $db */
        $db = app(DatabaseManager::class);
        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->with($relationships)->findOrFail($id)
            : $db->table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        foreach ($dataType->editRows as $key => $row) {
            $details = json_decode($row->details);
            $dataType->editRows[$key]['col_width'] = isset($details->width) ? $details->width : 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'edit');

        // Check permission
        $this->authorize('edit', $dataTypeContent);

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);
        $view = voyager()->getBreadView('edit-add', $slug);

        return voyager()->view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }
}

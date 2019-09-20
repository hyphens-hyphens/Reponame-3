<?php

namespace T2G\Common\Controllers;

use Illuminate\Http\Request;
use T2G\Common\Repository\UserRepository;

/**
 * Class AutocompleteController
 *
 * @package \App\Http\Controllers
 */
class AutoCompleteController extends Controller
{
    public function getUsers(Request $request, UserRepository $userRepository)
    {
        $term = $request->get('term');
        $users = $userRepository->getAutoCompleteUsers($term, 50);

        // select2 data format
        return response()->json(['results' => $users]);
    }
}

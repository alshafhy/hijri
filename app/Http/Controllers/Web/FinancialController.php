<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Financial\BuildFinancialBoardAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Financial\FinancialBoardRequest;
use Illuminate\View\View;

class FinancialController extends Controller
{
    public function index(FinancialBoardRequest $request, BuildFinancialBoardAction $action): View
    {
        $month = $request->validated('month');
        $board = $action($month);

        return view('financial.index', [
            'month' => $board['month'],
            'rows' => $board['rows'],
            'summary' => $board['summary'],
        ]);
    }
}

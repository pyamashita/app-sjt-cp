<?php

namespace App\Http\Controllers;

use App\Models\GuidePage;
use App\Models\Competition;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index(Request $request)
    {
        // 大会IDがパラメータで指定されている場合はその大会のアクティブページを取得
        if ($request->has('competition_id')) {
            $guidePage = GuidePage::getActiveForCompetition($request->competition_id);
        } else {
            // デフォルトは最初に見つかったアクティブページを表示
            $guidePage = GuidePage::where('is_active', true)
                ->with(['competition', 'sections.groups.items.resource'])
                ->first();
        }

        if (!$guidePage) {
            return view('guide.not-found');
        }

        return view('guide.index', compact('guidePage'));
    }

    public function competition(Competition $competition)
    {
        $guidePage = GuidePage::getActiveForCompetition($competition->id);
        
        if (!$guidePage) {
            return view('guide.not-found', compact('competition'));
        }

        $guidePage->load(['sections.groups.items.resource']);
        return view('guide.index', compact('guidePage'));
    }
}

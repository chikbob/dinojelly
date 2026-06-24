<?php

namespace App\Http\Controllers;

use App\Services\RecommendationAssistantService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(
        protected RecommendationAssistantService $recommendationAssistantService,
    ) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'occasion' => ['required', 'in:gift,party,kids,self'],
            'taste' => ['required', 'in:sour,fruity,light,surprise'],
            'budget' => ['required', 'numeric', 'min:0'],
            'format' => ['required', 'in:set,single,variety'],
            'priority' => ['required', 'in:popular,new,value'],
            'locale' => ['nullable', 'in:ru,en'],
        ]);

        app()->setLocale($data['locale'] ?? config('app.locale'));

        return response()->json(
            $this->recommendationAssistantService->recommend($request->user(), collect($data)->except('locale')->all())
        );
    }
}

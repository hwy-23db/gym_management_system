<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingSetting;
use App\Models\TrainerPricing;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        $trainers = User::query()
            ->where('role', 'trainer')
            ->orderBy('name')
            ->get();

        $pricingSetting = PricingSetting::firstOrCreate(
            [],
            ['monthly_subscription_price' => 80000]
        );

        $trainerPrices = TrainerPricing::query()
            ->whereIn('trainer_id', $trainers->pluck('id'))
            ->get()
            ->keyBy('trainer_id');

        return view('pages.pricing', [
            'trainers' => $trainers,
            'monthlyPrice' => $pricingSetting->monthly_subscription_price,
            'defaultTrainerPrice' => 30000,
            'trainerPrices' => $trainerPrices,
        ]);
    }

    public function updateMonthly(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_subscription_price' => ['required', 'numeric', 'min:0'],
        ]);

        $pricingSetting = PricingSetting::firstOrCreate(
            [],
            ['monthly_subscription_price' => 80000]
        );

        $pricingSetting->update($validated);

        return back()->with('status', 'Monthly subscription price updated.');
    }

    public function updateTrainer(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'trainer') {
            abort(404);
        }

        $validated = $request->validate([
            'price_per_session' => ['required', 'numeric', 'min:0'],
        ]);

        TrainerPricing::updateOrCreate(
            ['trainer_id' => $user->id],
            ['price_per_session' => $validated['price_per_session']]
        );

        return back()->with('status', "Session price updated for {$user->name}.");
    }
}

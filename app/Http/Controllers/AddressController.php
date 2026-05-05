<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'building' => 'required|string|max:255',
            'apartment' => 'nullable|string|max:50',
            'entrance' => 'nullable|string|max:50',
            'floor' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:50',
            'comment' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
        ]);

        if (! empty($validated['is_default'])) {
            Address::query()
                ->where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $request->user()->addresses()->create($validated);

        return back()->with('success', 'Address created');
    }

    public function update(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'building' => 'required|string|max:255',
            'apartment' => 'nullable|string|max:50',
            'entrance' => 'nullable|string|max:50',
            'floor' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:50',
            'comment' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
        ]);

        if (! empty($validated['is_default'])) {
            Address::query()
                ->where('user_id', $request->user()->id)
                ->whereKeyNot($address->id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('success', 'Address updated');
    }

    public function destroy(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->oldest()->limit(1)->update(['is_default' => true]);
        }

        return back()->with('success', 'Address deleted');
    }
}

<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'division' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'upazila' => ['nullable', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:500'],
            'type' => ['required', 'in:home,office'],
            'is_default_shipping' => ['nullable', 'boolean'],
            'is_default_billing' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $isFirst = $user->addresses()->count() === 0;

        $isDefaultShipping = $request->boolean('is_default_shipping') || $isFirst;
        $isDefaultBilling = $request->boolean('is_default_billing') || $isFirst;

        if ($isDefaultShipping) {
            $user->addresses()->where('is_default_shipping', true)->update(['is_default_shipping' => false]);
        }

        if ($isDefaultBilling) {
            $user->addresses()->where('is_default_billing', true)->update(['is_default_billing' => false]);
        }

        $address = $user->addresses()->create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'division' => $validated['division'],
            'district' => $validated['district'],
            'upazila' => $validated['upazila'] ?? null,
            'address_line' => $validated['address_line'],
            'type' => $validated['type'],
            'is_default_shipping' => $isDefaultShipping,
            'is_default_billing' => $isDefaultBilling,
        ]);

        return back()->with('success', 'Address saved successfully!')->with('selected_address_id', $address->id);
    }

    public function destroy(Address $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Address deleted successfully.');
    }
}

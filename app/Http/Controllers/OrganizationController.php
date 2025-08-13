<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Organization::class);

        return Inertia::render('Admin/Organizations', [
            'paginatedOrganizations' => Organization::orderBy('name', 'asc')->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Gate::authorize('create', Organization::class);

        return Inertia::render('Admin/CreateOrganization', [
            'defaultHourlyRate' => config('app.hourly_rate'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Organization::class);

        $validated = $request->validate([
            'name' => 'required',
            'client_id' => 'required|max:4|unique:organizations',
            'stripe_customer_id' => 'nullable|string',
            'billing_contact' => 'required',
            'email' => 'required',
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable',
            'hourly_rate' => 'required|integer',
        ]);

        $organization = Organization::create([
            'name' => $validated['name'],
            'client_id' => $validated['client_id'],
            'stripe_customer_id' => $validated['stripe_customer_id'] ?? null,
            'billing_contact' => $validated['billing_contact'],
            'email' => $validated['email'],
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip_code' => $validated['zip_code'],
            'hourly_rate' => $validated['hourly_rate'],
        ]);

        return redirect()->route('admin.organizations.index')->with('message', 'Organization created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization)
    {
        Gate::authorize('viewStats', $organization);

        return Inertia::render('Admin/ShowOrganization', [
            'organization' => $organization,
            'stats' => OrganizationService::organizationStats($organization),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Organization $organization)
    {
        Gate::authorize('update', $organization);

        return Inertia::render('Admin/EditOrganization', [
            'organization' => $organization,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Organization $organization)
    {
        Gate::authorize('update', $organization);

        $validated = $request->validate([
            'name' => 'required',
            'stripe_customer_id' => 'nullable|string',
            'billing_contact' => 'required',
            'email' => 'required',
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable',
            'hourly_rate' => 'required|integer',
        ]);

        $organization->name = $validated['name'];
        $organization->stripe_customer_id = $validated['stripe_customer_id'] ?? null;
        $organization->billing_contact = $validated['billing_contact'];
        $organization->email = $validated['email'];
        $organization->address_line_1 = $validated['address_line_1'];
        $organization->address_line_2 = $validated['address_line_2'];
        $organization->city = $validated['city'];
        $organization->state = $validated['state'];
        $organization->zip_code = $validated['zip_code'];
        $organization->hourly_rate = $validated['hourly_rate'];
        $organization->save();

        return back()->with('message', 'Organization updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization)
    {
        Gate::authorize('delete', $organization);
    }

    public function enableStripe(Organization $organization)
    {
        Gate::authorize('update', $organization);

        if ($organization->stripe_customer_id) {
            return back()->with('error', 'Cannot re-enable Stripe. Organization already has a Stripe Customer ID.');
        }

        OrganizationService::createStripeCustomer($organization);

        // return to_route('admin.organizations.index')->with('message', 'Stripe enabled for organization!');
        return back()->with('message', 'Stripe enabled for organization!');
    }

    // Set organization scope
    public function changeOrganization(Request $request)
    {
        if ($request->organization_id) {
            $organization = Organization::findOrFail($request->organization_id);
            Gate::authorize('view', $organization);
        }

        $previousActiveOrganization = session('activeOrganization');
        session(['activeOrganization' => $request->organization_id]);

        if ($previousActiveOrganization && session('activeOrganization')) {
            return back();
        }

        return redirect()->route(session('activeOrganization') ? 'dashboard' : 'admin.dashboard');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        return Inertia::render('Admin/Users', [
            'users' => User::with('organizations')->orderBy('name', 'asc')->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Admin/CreateUser', [
            'organizations' => Organization::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        // 'preferences',
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'is_admin' => 'required|boolean',
            'organization_id' => 'nullable|integer',
        ]);

        $password = config('app.env') === 'local' ? 'password' : Str::random(16);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'is_admin' => $validated['is_admin'],
        ]);

        if ($validated['organization_id']) {
            $user->organizations()->attach($validated['organization_id']);
        }

        return redirect()->route('admin.users.index')->with('message', 'User created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        Gate::authorize('view', $user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        return Inertia::render('Admin/EditUser', [
            'user' => $user->load('organizations'),
            'organizations' => Organization::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'is_admin' => 'required|boolean',
            'organization_id' => 'nullable|integer',
            'invoice_notification' => 'nullable',
            'account_notification' => 'nullable',
            'account_notification_day' => 'nullable|integer|max:6',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_admin = $validated['is_admin'];

        if ($user->is_admin) {
            $user->preferences = null;
        } else {
            $user->preferences = [
                'invoice_notification_email' => $validated['invoice_notification'] === 'email',
                'invoice_notification_email_cc' => $validated['invoice_notification'] === 'cc',
                'account_notification_email' => $validated['account_notification'] === 'email',
                'account_notification_email_cc' => $validated['account_notification'] === 'cc',
                'account_notification_email_day' => $validated['account_notification_day'] ? ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$validated['account_notification_day']] : null,
            ];
        }
        
        $user->save();

        if ($validated['organization_id']) {
            if ($user->organizations->first()) {
                $user->organizations()->detach($user->organizations->first()->id);
            }

            $user->organizations()->attach($validated['organization_id']);
        }

        if ($user->is_admin) {
            $user->organizations()->detach($user->organizations->pluck('id'));
        }

        return redirect()->route('admin.users.index')->with('message', 'User updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);
        
        $user->delete();

        return back()->with('message', 'User deleted successfully!');
    }

    public function invite(User $user)
    {
        Gate::authorize('update', $user);

        $expiresAt = now()->addDays(3);
        $user->sendWelcomeNotification($expiresAt);

        return back()->with('message', 'User invitation created successfully!');
    }
}

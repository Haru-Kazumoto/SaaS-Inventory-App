<?php

namespace App\Http\Controllers\Identity;

use App\Domain\Identity\Actions\DeleteMember;
use App\Domain\Identity\Actions\SaveMember;
use App\Domain\Identity\Queries\MemberQuery;
use App\Domain\Organization\Support\Access;
use App\Domain\Organization\Support\CurrentOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\SaveMemberRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/** Anggota organisasi (khusus admin organisasi). */
class MemberController extends Controller
{
    public function index(MemberQuery $query): Response
    {
        Access::authorizeOrganization();

        return Inertia::render('Members/Index', ['members' => $query->list()]);
    }

    public function create(MemberQuery $query): Response
    {
        Access::authorizeOrganization();

        return Inertia::render('Members/Create', $query->form());
    }

    public function store(SaveMemberRequest $request, SaveMember $save): RedirectResponse
    {
        $member = $save->handle($request->toData());

        return redirect()->route('members.index')->with('success', "Anggota {$member->fullname} ditambahkan");
    }

    public function edit(User $member, MemberQuery $query): Response
    {
        Access::authorizeOrganization();
        $this->ensureSameOrganization($member);

        return Inertia::render('Members/Edit', $query->form($member));
    }

    public function update(SaveMemberRequest $request, User $member, SaveMember $save): RedirectResponse
    {
        $this->ensureSameOrganization($member);
        $save->handle($request->toData(), $member);

        return redirect()->route('members.index')->with('success', "Data {$member->fullname} diperbarui");
    }

    public function destroy(User $member, DeleteMember $delete): RedirectResponse
    {
        $delete->handle($member);

        return back()->with('success', "Anggota {$member->fullname} dihapus");
    }

    private function ensureSameOrganization(User $member): void
    {
        abort_unless($member->organization_id === CurrentOrganization::id(), 404);
    }
}

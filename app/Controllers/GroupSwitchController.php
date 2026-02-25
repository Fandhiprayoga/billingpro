<?php

namespace App\Controllers;

class GroupSwitchController extends BaseController
{
    /**
     * Switch active group untuk user yang sedang login.
     * User hanya bisa switch ke group yang memang dimilikinya.
     */
    public function switch()
    {
        $group = $this->request->getPost('group');

        if (empty($group)) {
            return redirect()->back()->with('error', 'Group tidak valid.');
        }

        $user       = auth()->user();
        $userGroups = $user->getGroups();

        // Pastikan user memiliki group yang dipilih
        if (! in_array($group, $userGroups)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki role tersebut.');
        }

        // Set active group di session
        session()->set('active_group', $group);

        $authGroups = config('AuthGroups');
        $groupTitle = $authGroups->groups[$group]['title'] ?? ucfirst($group);

        return redirect()->to('/dashboard')->with('success', "Role aktif diubah ke: {$groupTitle}");
    }
}

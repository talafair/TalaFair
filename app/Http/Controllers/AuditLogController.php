<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $types = [
            'Attendance' => 'Attendance',
            'Announcement' => 'Announcements',
            'Badge' => 'Badges',
            'EventRsvp' => 'Event RSVP',
            'TriviaTheme' => 'Trivia themes',
            'Survey' => 'Surveys',
            'User' => 'User accounts',
        ];
        $actions = ['login' => 'Login', 'created' => 'Created', 'updated' => 'Updated', 'password_updated' => 'Password updated', 'deleted' => 'Deleted'];
        $userName = trim($request->string('user_name')->toString());

        $logs = AuditLog::with(['user', 'auditable'])
            ->when($userName !== '', function ($query) use ($userName) {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%' . $userName . '%'));
            })
            ->when($request->filled('type') && isset($types[$request->string('type')->toString()]), function ($query) use ($request) {
                $query->where('auditable_type', 'like', '%' . $request->string('type')->toString());
            })
            ->when($request->filled('action') && isset($actions[$request->string('action')->toString()]), function ($query) use ($request) {
                $query->where('action', $request->string('action')->toString());
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('pages.audit-logs', compact('logs', 'types', 'actions'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoChecklist;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TodoController extends Controller
{
    /**
     * Dashboard Kanban / List To-Do
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Todo::with(['assignee', 'creator', 'shipment', 'checklists']);

        // Filter status tab
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Scope filter: my tasks vs delegated by me vs all (if executive)
        $viewScope = $request->get('scope', 'my_tasks');
        if ($viewScope === 'my_tasks') {
            $query->where('assigned_to_user_id', $user->id);
        } elseif ($viewScope === 'delegated') {
            $query->where('created_by_user_id', $user->id)->where('assigned_to_user_id', '!=', $user->id);
        } elseif ($viewScope === 'all') {
            if (!in_array($user->role ?? '', ['R-OWN', 'R-MGR', 'R-SYS'])) {
                $query->where(function($q) use ($user) {
                    $q->where('assigned_to_user_id', $user->id)
                      ->orWhere('created_by_user_id', $user->id);
                });
            }
        }

        $todos = $query->orderBy('due_date', 'asc')->orderBy('priority', 'desc')->get();

        // Grouping for Kanban Board
        $kanban = [
            'TODO' => $todos->where('status', 'TODO'),
            'IN_PROGRESS' => $todos->where('status', 'IN_PROGRESS'),
            'DONE' => $todos->where('status', 'DONE'),
        ];

        $users = User::where('is_active', true)->orderBy('name')->get();
        $shipments = Shipment::where('status', 'ACTIVE')->orderBy('nomor_aju')->limit(50)->get();

        return view('core.todos.index', compact('todos', 'kanban', 'users', 'shipments', 'viewScope'));
    }

    /**
     * Simpan Tugas Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,URGENT',
            'due_date' => 'nullable|date',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'shipment_id' => 'nullable|exists:shipments,id',
            'category' => 'required|string',
            'is_recurring' => 'nullable|boolean',
            'recurrence_interval' => 'nullable|string',
            'checklist_items' => 'nullable|array',
            'checklist_items.*' => 'nullable|string|max:255',
        ]);

        $todo = DB::transaction(function () use ($validated) {
            $user = auth()->user();
            $todo = Todo::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['priority'],
                'status' => 'TODO',
                'due_date' => $validated['due_date'] ?? null,
                'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? $user->id,
                'created_by_user_id' => $user->id,
                'shipment_id' => $validated['shipment_id'] ?? null,
                'category' => $validated['category'] ?? 'OPERATIONAL',
                'is_recurring' => $validated['is_recurring'] ?? false,
                'recurrence_interval' => $validated['recurrence_interval'] ?? null,
            ]);

            if (!empty($validated['checklist_items'])) {
                foreach ($validated['checklist_items'] as $item) {
                    if (trim($item)) {
                        TodoChecklist::create([
                            'todo_id' => $todo->id,
                            'item_text' => trim($item),
                            'is_completed' => false,
                        ]);
                    }
                }
            }

            return $todo;
        });

        return redirect()->route('todos.index')->with('success', 'Tugas berhasil dibuat dan didelegasikan.');
    }

    /**
     * Update Status Tugas (Drag & Drop Kanban atau Klik Selesai)
     */
    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:TODO,IN_PROGRESS,WAITING_EXTERNAL,DONE,CANCELLED',
        ]);

        $todo = Todo::findOrFail($id);
        $todo->status = $validated['status'];
        if ($validated['status'] === 'DONE') {
            $todo->completed_at = now();
        } else {
            $todo->completed_at = null;
        }
        $todo->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $todo->status]);
        }

        return back()->with('success', 'Status tugas berhasil diperbarui.');
    }

    /**
     * Toggle Checklist Item
     */
    public function toggleChecklist(string $id)
    {
        $item = TodoChecklist::findOrFail($id);
        $item->is_completed = !$item->is_completed;
        $item->completed_by_user_id = $item->is_completed ? auth()->id() : null;
        $item->completed_at = $item->is_completed ? now() : null;
        $item->save();

        return response()->json(['success' => true, 'is_completed' => $item->is_completed]);
    }

    /**
     * Delegasikan Tugas ke Rekan Lain
     */
    public function delegate(Request $request, string $id)
    {
        $validated = $request->validate([
            'assigned_to_user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string',
        ]);

        $todo = Todo::findOrFail($id);
        $oldAssignee = $todo->assignee?->name ?? 'Belum Ditugaskan';
        $todo->assigned_to_user_id = $validated['assigned_to_user_id'];
        $todo->save();

        $newAssignee = User::find($validated['assigned_to_user_id'])->name;

        return back()->with('success', "Tugas berhasil dialihkan dari {$oldAssignee} ke {$newAssignee}.");
    }
}

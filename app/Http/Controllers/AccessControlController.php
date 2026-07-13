<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccessControlController extends Controller
{
    public function index()
    {
        return view('employees.access-control');
    }

    public function getUsers(Request $request)
    {
        if ($request->ajax()) {
            $data = User::with('employee')->select('users.*');
            
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('linked_employee', function($row) {
                    return $row->employee ? $row->employee->name : '<span class="text-muted">None</span>';
                })
                ->addColumn('role_badge', function($row) {
                    $colors = [
                        'admin' => 'danger',
                        'manager' => 'primary',
                        'team_lead' => 'info',
                        'employee' => 'secondary'
                    ];
                    $color = $colors[$row->role] ?? 'secondary';
                    return '<span class="badge bg-'.$color.' text-uppercase">'.str_replace('_', ' ', $row->role).'</span>';
                })
                ->addColumn('action', function($row) {
                    $json = htmlspecialchars(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'email' => $row->email,
                        'role' => $row->role,
                        'employee_id' => $row->employee ? $row->employee->id : null
                    ]), ENT_QUOTES, 'UTF-8');
                    
                    return '
                        <button class="action-btn edit-role-btn" data-user="'.$json.'" title="Edit Access">
                            <i class="fa-solid fa-shield-halved text-primary"></i>
                        </button>
                    ';
                })
                ->rawColumns(['linked_employee', 'role_badge', 'action'])
                ->make(true);
        }
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,employee',
            'employee_id' => 'nullable|exists:employees,id'
        ]);

        $user->update(['role' => $request->role]);

        // If linking an employee, update the employee's user_id
        if ($request->employee_id) {
            // Unlink any other employee linked to this user
            Employee::where('user_id', $user->id)->update(['user_id' => null]);
            
            // Link the new employee
            Employee::where('id', $request->employee_id)->update(['user_id' => $user->id]);
        } else {
            // Unlink
            Employee::where('user_id', $user->id)->update(['user_id' => null]);
        }

        return redirect()->back()->with('success', 'User access updated successfully.');
    }
}

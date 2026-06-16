<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Get employees data for DataTables.
     */
    public function getEmployees(Request $request)
    {
        if ($request->ajax()) {
            $data = Employee::select(['id', 'name', 'email', 'team']);
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addColumn('action', function($row){
                    $empJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    return '
                        <button class="action-btn view-performance-btn" data-id="'.$row->id.'" title="Performance">
                            <i class="fa-solid fa-chart-bar"></i>
                        </button>
                        <button class="action-btn edit-emp-btn" data-emp="'.$empJson.'" title="Edit Employee">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="action-btn delete-emp-btn trigger-delete-emp" data-action="'.route('employees.destroy', $row->id).'" title="Delete Employee">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function index()
    {
        return view('employees');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'team' => 'required|string',
        ]);
        Employee::create($validated);
        return redirect()->back()->with('success', 'Employee added successfully.');
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'team' => 'required|string',
        ]);
        $employee->update($validated);
        return redirect()->back()->with('success', 'Employee updated successfully.');
    }

    public function details(Employee $employee)
    {
        $employee->load(['tasks' => function($q) {
            $q->orderBy('created_at', 'desc');
        }]);

        return response()->json([
            'employee' => $employee,
            'tasks' => $employee->tasks
        ]);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->back()->with('success', 'Employee deleted successfully.');
    }
}

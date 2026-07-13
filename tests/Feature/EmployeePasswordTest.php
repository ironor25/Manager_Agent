<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_employee_with_generated_password_and_user_mapping()
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
        ]);

        // Use generic fake file creator which doesn't require GD library
        $photoFile = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($admin)
            ->post(route('employees.store'), [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'team' => 'Engineering',
                'designation' => 'Software Engineer',
                'status' => 'active',
                'join_date' => '2026-06-29',
                'photo' => $photoFile,
            ]);

        $response->assertStatus(302);

        // Verify employee record was created with raw password and photo path
        $employee = Employee::where('email', 'john@example.com')->first();
        $this->assertNotNull($employee);
        $this->assertNotNull($employee->password);
        $this->assertEquals(8, strlen($employee->password));
        $this->assertTrue(is_numeric($employee->password));
        $this->assertNotNull($employee->photo);

        // Verify file was stored on disk
        Storage::disk('public')->assertExists($employee->photo);

        // Verify user record was created with hashed password, role employee, and profile image
        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('employee', $user->role);
        $this->assertEquals($employee->photo, $user->profile_image);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($employee->password, $user->password));

        // Verify the mapping
        $this->assertEquals($user->id, $employee->user_id);
    }

    public function test_can_edit_employee_and_update_passwords_and_photos()
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
        ]);

        // Upload initial photo
        $initialPhoto = UploadedFile::fake()->create('initial.jpg', 100, 'image/jpeg')->store('employee_photos', 'public');

        // Create an employee first
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            'profile_image' => $initialPhoto,
            'role' => 'employee',
        ]);

        $employee = Employee::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'user_id' => $user->id,
            'password' => '12345678',
            'photo' => $initialPhoto,
            'status' => 'active',
        ]);

        Storage::disk('public')->assertExists($initialPhoto);

        // Upload new photo
        $newPhotoFile = UploadedFile::fake()->create('updated.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($admin)
            ->put(route('employees.update', $employee->id), [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'team' => 'Product',
                'designation' => 'Product Manager',
                'status' => 'active',
                'join_date' => '2026-06-29',
                'password' => 'newpassword888',
                'photo' => $newPhotoFile,
            ]);

        $response->assertStatus(302);

        // Verify employee record has updated raw password and new photo
        $employee->refresh();
        $this->assertEquals('newpassword888', $employee->password);
        $this->assertEquals('jane.smith@example.com', $employee->email);
        $this->assertNotEquals($initialPhoto, $employee->photo);

        // Verify new photo is stored and old photo is deleted
        Storage::disk('public')->assertExists($employee->photo);
        Storage::disk('public')->assertMissing($initialPhoto);

        // Verify user record has updated email, profile image, and hashed password
        $user->refresh();
        $this->assertEquals('jane.smith@example.com', $user->email);
        $this->assertEquals($employee->photo, $user->profile_image);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword888', $user->password));
    }

    public function test_login_redirects_admin_to_admin_dashboard()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_login_redirects_employee_to_employee_dashboard()
    {
        $employeeUser = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'employee',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'employee@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/employee/dashboard');
    }

    public function test_employee_cannot_access_admin_dashboard()
    {
        $employeeUser = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employeeUser)->get('/admin/dashboard');
        $response->assertRedirect('/');
    }

    public function test_admin_cannot_access_employee_dashboard()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/employee/dashboard');
        $response->assertRedirect('/');
    }

    public function test_employee_can_access_all_portal_pages()
    {
        $employeeUser = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'employee',
        ]);

        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'team' => 'Product',
            'designation' => 'QA Engineer',
            'status' => 'active',
        ]);

        $response = $this->actingAs($employeeUser)->get('/employee/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('employee.dashboard');

        $response = $this->actingAs($employeeUser)->get('/employee/tasks');
        $response->assertStatus(200);
        $response->assertViewIs('employee.tasks');

        $response = $this->actingAs($employeeUser)->get('/employee/leaderboard');
        $response->assertStatus(200);
        $response->assertViewIs('employee.leaderboard');

        $response = $this->actingAs($employeeUser)->get('/employee/commits');
        $response->assertStatus(200);
        $response->assertViewIs('employee.commits');

        $response = $this->actingAs($employeeUser)->get('/employee/meetings');
        $response->assertStatus(200);
        $response->assertViewIs('employee.meetings');

        $response = $this->actingAs($employeeUser)->get('/employee/attendance');
        $response->assertStatus(200);
        $response->assertViewIs('employee.attendance');
    }

    public function test_employee_portal_datatables_endpoints_return_valid_json()
    {
        $employeeUser = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'employee',
        ]);

        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'team' => 'Product',
            'designation' => 'QA Engineer',
            'status' => 'active',
        ]);

        // Tasks Data
        $response = $this->actingAs($employeeUser)->getJson('/employee/tasks/data');
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        // Leaderboard Data
        $response = $this->actingAs($employeeUser)->getJson('/employee/leaderboard/data');
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        // Commits Data
        $response = $this->actingAs($employeeUser)->getJson('/employee/commits/data');
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        // Meetings Data
        $response = $this->actingAs($employeeUser)->getJson('/employee/meetings/data');
        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        // Attendance Data (standard JSON list)
        $response = $this->actingAs($employeeUser)->getJson('/employee/attendance/data');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'date',
                'status',
                'login_time',
                'logout_time',
            ]
        ]);
    }

    public function test_employee_can_update_profile_and_syncs_with_employee_record()
    {
        $employeeUser = User::create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('oldpassword'),
            'role' => 'employee',
        ]);

        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => 'oldpassword',
            'status' => 'active',
        ]);

        $response = $this->actingAs($employeeUser)->post(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('profile.edit'));

        // Assert user record was updated
        $employeeUser->refresh();
        $this->assertEquals('New Name', $employeeUser->name);
        $this->assertEquals('new@example.com', $employeeUser->email);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $employeeUser->password));

        // Assert employee record was updated
        $employee->refresh();
        $this->assertEquals('New Name', $employee->name);
        $this->assertEquals('new@example.com', $employee->email);
        $this->assertEquals('newpassword123', $employee->password);
    }
}

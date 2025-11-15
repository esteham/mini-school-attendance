<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\StudentUpdateRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('class')) {
            $query->where('class', $request->class);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('student_id', 'like', "%$search%");
            });
        }

        $students = $query->orderBy('class')->orderBy('section')->paginate(10);

        return StudentResource::collection($students);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentStoreRequest $request)
    {
        $student = Student::create($request->validated());

        return new StudentResource($student);
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        return new StudentResource($student);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentUpdateRequest $request, Student $student)
    {
        $student->update($request->validated());

        return new StudentResource($student);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json(['message' => 'Deleted']);
    }
}

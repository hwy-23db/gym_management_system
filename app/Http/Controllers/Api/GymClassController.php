<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GymClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GymClassController extends Controller
{
    public function index(): JsonResponse
    {
        $classes = GymClass::query()->orderBy('id')->get();

        return response()->json([
            'data' => $classes->map(fn (GymClass $gymClass) => $this->formatClass($gymClass)),
        ]);
    }

    public function show(GymClass $gymClass): JsonResponse
    {
        return response()->json([
            'data' => $this->formatClass($gymClass),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateClass($request);
        $gymClass = GymClass::create($validated);

        return response()->json([
            'message' => 'Class created successfully.',
            'data' => $this->formatClass($gymClass),
        ], 201);
    }

    public function update(Request $request, GymClass $gymClass): JsonResponse
    {
        $validated = $this->validateClass($request, true);

        $gymClass->update($validated);

        return response()->json([
            'message' => 'Class updated successfully.',
            'data' => $this->formatClass($gymClass->fresh()),
        ]);
    }

    public function destroy(GymClass $gymClass): JsonResponse
    {
        $gymClass->delete();

        return response()->json([
            'message' => 'Class deleted successfully.',
        ]);
    }

    private function validateClass(Request $request, bool $isUpdate = false): array
    {
        $requiredRule = $isUpdate ? 'sometimes' : 'required';

        return $request->validate([
            'class_name' => [$requiredRule, 'string', 'max:255'],
            'class_day' => [$requiredRule, 'string', 'max:50'],
            'class_time' => [$requiredRule, 'date_format:H:i'],
        ]);
    }

    private function formatClass(GymClass $gymClass): array
    {
        return [
            'id' => $gymClass->id,
            'class_name' => $gymClass->class_name,
            'class_day' => $gymClass->class_day,
            'class_time' => $gymClass->class_time,
            'created_at' => $gymClass->created_at?->toIso8601String(),
            'updated_at' => $gymClass->updated_at?->toIso8601String(),
        ];
    }
}

<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTestSuiteRequest;
use App\Http\Requests\Api\V1\UpdateTestSuiteRequest;
use App\Http\Resources\V1\TestSuiteResource;
use App\Models\Project;
use App\Models\TestSuite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestSuiteController extends Controller
{
    public function index(Project $project): AnonymousResourceCollection
    {
        $suites = $project->testSuites()->with('project')->latest()->paginate(25);

        return TestSuiteResource::collection($suites);
    }

    public function show(TestSuite $suite): TestSuiteResource
    {
        $suite->load('project');

        return new TestSuiteResource($suite);
    }

    public function store(StoreTestSuiteRequest $request, Project $project): JsonResponse
    {
        $suite = $project->testSuites()->create($request->validated());

        return (new TestSuiteResource($suite->load('project')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTestSuiteRequest $request, TestSuite $suite): TestSuiteResource
    {
        $suite->update($request->validated());

        return new TestSuiteResource($suite->fresh()->load('project'));
    }

    public function destroy(TestSuite $suite): JsonResponse
    {
        $suite->delete();

        return response()->json(['message' => 'Test suite deleted.']);
    }
}

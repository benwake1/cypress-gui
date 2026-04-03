<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClientRequest;
use App\Http\Requests\Api\V1\UpdateClientRequest;
use App\Http\Resources\V1\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $clients = Client::withCount('projects')->latest()->paginate(25);

        return ClientResource::collection($clients);
    }

    public function show(Client $client): ClientResource
    {
        $client->loadCount('projects');

        return new ClientResource($client);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $client->update($request->validated());

        return new ClientResource($client->fresh());
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json(['message' => 'Client deleted.']);
    }
}

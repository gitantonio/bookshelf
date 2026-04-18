<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Resources\PublisherResource;
use App\Models\Publisher;
use Illuminate\Http\Request;

/**
 * @group Publishers
 */
class PublisherController extends Controller
{
    /**
     * List publishers
     *
     * @unauthenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Results per page (max 100). Example: 15
     *
     * @apiResourceCollection App\Http\Resources\PublisherResource
     * @apiResourceModel App\Models\Publisher paginate=15
     *
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function index()
    {
        $this->authorize('viewAny', Publisher::class);

        $publishers = Publisher::paginate(15);

        return PublisherResource::collection($publishers);
    }

    /**
     * Create a publisher
     *
     * @authenticated
     *
     * @bodyParam name string required Publisher's name (max 255). Example: Bompiani
     * @bodyParam country string required Publisher's country (max 100). Example: IT
     * @bodyParam website string Website URL (max 255). Example: https://www.bompiani.it
     *
     * @apiResource status=201 App\Http\Resources\PublisherResource
     * @apiResourceModel App\Models\Publisher
     *
     * @response 422 scenario="Validation failed" {"message":"The name field is required.","errors":{"name":["The name field is required."]}}
     */
    public function store(Request $request)
    {
        $this->authorize('create', Publisher::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $publisher = Publisher::create($validated);

        return (new PublisherResource($publisher))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show a publisher
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\PublisherResource
     * @apiResourceModel App\Models\Publisher
     *
     * @response 404 {"message":"No query results for model [App\\Models\\Publisher]."}
     */
    public function show(Publisher $publisher)
    {
        $this->authorize('view', $publisher);

        return new PublisherResource($publisher);
    }

    /**
     * Update a publisher
     *
     * @authenticated
     *
     * @bodyParam name string Publisher's name (max 255). Example: Bompiani
     * @bodyParam country string Publisher's country (max 255). Example: IT
     * @bodyParam website string Website URL (max 255). Example: https://www.bompiani.it
     *
     * @apiResource App\Http\Resources\PublisherResource
     * @apiResourceModel App\Models\Publisher
     *
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function update(Request $request, Publisher $publisher)
    {
        $this->authorize('update', $publisher);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'country' => ['sometimes', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $publisher->update($validated);

        return new PublisherResource($publisher);
    }

    /**
     * Delete a publisher
     *
     * Fails with 422 if the publisher still has books.
     *
     * @authenticated
     *
     * @response 204 {}
     * @response 422 scenario="Publisher has books" {"message":"Cannot delete a publisher that has books."}
     */
    public function destroy(Publisher $publisher)
    {
        $this->authorize('delete', $publisher);

        if ($publisher->books()->exists()) {
            throw new BusinessException(
                'Cannot delete a publisher that has books.'
            );
        }

        $publisher->delete();

        return response()->json(null, 204);
    }
}
